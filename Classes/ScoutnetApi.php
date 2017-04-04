<?php
namespace ScoutNet\Api;

use ScoutNet\Api\Models\Event;
use ScoutNet\Api\Models\Index;
use ScoutNet\Api\Models\Kalender;
use ScoutNet\Api\Models\Stufe;
use ScoutNet\Api\Models\User;

use ScoutNet\Api\Helpers\AesHelper;
use ScoutNet\Api\Helpers\JsonRPCClientHelper;

/***************************************************************
*  Copyright notice
*
*  (c) 2009 Stefan Horst <muetze@scoutnet.de>
*  All rights reserved
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

class ScoutnetApi {
	var $iv = '1234567890123456';

	var $SN = null;

	var $user_cache = array();
	var $stufen_cache = array();
	var $kalender_cache = array();

	var $snData;

	private $provider = null;
	private $aes_key = null;
	private $aes_iv = null;

	private $login_url = null;

    /**
     *
     * @param string $api_url
     * @param string $login_url
     * @param string $provider
     * @param string $aes_key
     * @param string $aes_iv
     */
	public function __construct($api_url = "https://www.scoutnet.de/jsonrpc/server.php", $login_url = 'https://www.scoutnet.de/community/scoutnetConnect.html', $provider = '', $aes_key = '', $aes_iv = '') {
		//ini_set('default_socket_timeout',1);
		$this->SN = new JsonRPCClientHelper($api_url);

		$this->set_scoutnet_connect_data($login_url, $provider, $aes_key, $aes_iv);
	}

    /**
     * @param string $login_url
     * @param string $provider
     * @param string $aes_key
     * @param string $aes_iv
     * @throws ScoutnetException_MissingConfVar
     */
	public function set_scoutnet_connect_data($login_url = null, $provider, $aes_key, $aes_iv){
	    if ($login_url == null) {
	        $login_url = 'https://www.scoutnet.de/community/scoutnetConnect.html';
        }
	    $provider = trim($provider);

        $this->login_url = $login_url;
	    $this->provider = $provider;
	    $this->aes_key = $aes_key;
	    $this->aes_iv = $aes_iv;

        // if one is set all must be set correct
        if ($provider != '' || $aes_key !== '' || $aes_iv !== '') {
            $this->_check_for_all_configValues();
        }
	}

    /**
     * checks if ScoutNet connect Values are set correctly
     *
     * @throws ScoutnetException_MissingConfVar
     */
    private function _check_for_all_configValues(){
        if (trim($this->aes_key) == '' || strlen($this->aes_key) != 32) {
            throw new ScoutnetException_MissingConfVar('aes_key');
        }
        if (trim($this->aes_iv) == '' || strlen($this->aes_iv) != 16) {
            throw new ScoutnetException_MissingConfVar('aes_iv');
        }
        if (trim($this->login_url) == '') {
            throw new ScoutnetException_MissingConfVar('login_url');
        }
        if (trim($this->provider) == '') {
            throw new ScoutnetException_MissingConfVar('provider');
        }
    }

	protected function load_data_from_scoutnet($ids,$query){
		$res = $this->SN->get_data_by_global_id($ids,$query);

		return $res;
	}

    /**
     * @param $ids
     * @param $filter
     * @return  Event[]
     */
	public function get_events_for_global_id_with_filter($ids,$filter){
		$events = array();
		foreach ($this->load_data_from_scoutnet($ids,array('events'=>$filter)) as $record) {

			if ($record['type'] === 'user'){
				$user = new User( $record['content']);
				$this->user_cache[$user['userid']] = $user;
			} elseif ($record['type'] === 'stufe'){
				$stufe = new Stufe($record['content']);
				$this->stufen_cache[$stufe['Keywords_ID']] = $stufe;
			} elseif ($record['type'] === 'kalender'){
				$kalender = new Kalender($record['content']);
				$this->kalender_cache[$kalender['ID']] = $kalender;
			} elseif ($record['type'] === 'event') {
				$event = new Event($record['content']);

				$author = $this->get_user_by_id($event['Last_Modified_By']);
				if ($author == null) {
					$author = $this->get_user_by_id($event['Created_By']);
				}

				if ($author != null) {
					$event['Author'] = $author;
				}	

				$stufen = Array();


				if (isset($event['Stufen'])){
					foreach ($event['Stufen'] as $stufenId) {
						$stufe = $this->get_stufe_by_id($stufenId);
						if ($stufe != null) {
							$stufen[] = $stufe;
						}
					}
				}

				$event['Stufen'] = $stufen;
					
				$event['Kalender'] = $this->get_kalender_by_id($event['Kalender']);


				$events[] = $event;
			}
		}
		return $events;
	}

    /**
     * @param $ids
     * @param $filter
     * @return Index[]
     */
	public function get_index_for_global_id_with_filter($ids, $filter){
		$indexes = array();
		foreach ($this->load_data_from_scoutnet($ids,array('index'=>$filter)) as $record) {

			if ($record['type'] === 'index'){
				$index = new Index($record['content']);
//				$index['parent'] = $indexes[$index['parent_id']];

				if (isset($indexes[$index['parent_id']])) $indexes[$index['parent_id']]->addChild($index);
				$indexes[$index['id']] = $index;
			}
		}
		return $indexes;
	}

	public function get_events_with_ids($ids,$event_ids){
		return $this->get_events_for_global_id_with_filter($ids,array('event_ids'=>$event_ids));
	}

	public function get_kalender_by_global_id($ids) {
		$kalenders = array();
		foreach ($this->load_data_from_scoutnet($ids,array('kalenders'=>array())) as $record) {
			if ($record['type'] === 'kalender'){
				$kalender = new Kalender($record['content']);
				$this->kalender_cache[$kalender['ID']] = $kalender;
				$kalenders[] = $kalender;
			}
		}

		return $kalenders;
	}

	private function get_stufe_by_id($id) {
		return $this->stufen_cache[$id];
	}

	private function get_kalender_by_id($id) {
		return $this->kalender_cache[$id];
	}

	private function get_user_by_id($id) {
		return $this->user_cache[$id];
	}

	public function write_event($id,$data,$user,$api_key) {
		$type = 'event';
		$auth = $this->_generate_auth($api_key,$type.$id.serialize($data).$user);

		return $this->SN->setData($type,$id,$data,$user,$auth);
	}

	public function delete_event($ssid,$id,$user,$api_key) {
		$type = 'event';
		$auth = $this->_generate_auth($api_key,$type.$ssid.$id.$user);

		return $this->SN->deleteObject($type,$ssid,$id,$user,$auth);
	}

	public function has_write_permission_to_calender($ssid,$user,$api_key) {
		$type = 'event';
		$auth = $this->_generate_auth($api_key,$type.$ssid.$user);

		return $this->SN->checkPermission($type,$ssid,$user,$auth);
	}

	public function request_write_permissions_for_calender($ssid,$user,$api_key) {
		$type = 'event';
		$auth = $this->_generate_auth($api_key,$type.$ssid.$user);

		return $this->SN->requestPermission($type,$ssid,$user,$auth);
	}

    /**
     * returns the ScoutNet Connect Button in formated HTML
     *
     * @param string $returnUrl
     * @param bool $requestApiKey
     * @param string $imageURL
     * @param string $lang
     * @return string
     */
    public function get_scoutnet_connect_login_button($returnUrl = '', $requestApiKey = false, $imageURL = 'https://www.scoutnet.de/images/scoutnetConnect.png', $lang = 'de'){
        $this->_check_for_all_configValues();
        $button = '<form action="'.$this->login_url.'" id="scoutnetLogin" method="post" target="_self">'."\n";

        $button .= $returnUrl == ''?'':"    ".'<input type="hidden" name="redirect_url" value="'.$returnUrl.'" />'."\n";
        $button .= "    ".'<input type="hidden" name="lang" value="'.$lang.'"/>'."\n";
        $button .= "    ".'<input type="hidden" name="provider" value="'.$this->provider.'" />'."\n";
        $button .= $requestApiKey?("    ".'<input type="hidden" name="createApiKey" value="1" />'."\n"):'';

        $button .= "    ".'<a href="#" onclick="document.getElementById(\'scoutnetLogin\').submit(); return false;">'."\n";

        $button .= "        ".'<img src="'.$imageURL.'" title="Login with Scoutnet" alt="scoutnet Login"/>'."\n";
        $button .= "    ".'</a>'."\n";

        $button .= '</form>';

        return $button;
    }


	public function getApiKeyFromData(){
		if (isset($this->snData)) {
			return $this->snData;
		}    

		if (!isset($_GET['auth'])) {
			return false;
		}

		$this->_check_for_all_configValues();

		$z = $this->AES_key;
		$iv = $this->AES_iv;

		$aes = new tx_shscoutnetwebservice_AES($z,"CBC",$iv);

		$base64 = base64_decode(strtr($_GET['auth'], '-_~','+/='));

		if (trim($base64) == "")  
			throw new Exception('the auth is empty');

		$data = unserialize(substr($aes->decrypt($base64),strlen($iv)));


		$md5 = $data['md5']; unset($data['md5']);
		$sha1 = $data['sha1']; unset($data['sha1']);

		if (md5(serialize($data)) != $md5) {
			throw new Exception('the auth is broken');
		}    

		if (sha1(serialize($data)) != $sha1) {
			throw new Exception('the auth is broken');
		}    


		if (time() - $data['time'] > 3600) {
			throw new Exception('the auth is too old. Try again');
		}    

		$your_domain = $this->ScoutnetProviderName;

		if ($data['your_domain'] != $your_domain)
			throw new Exception('the auth is for the wrong site!. Try again');

		$this->snData = $data;

		return $data;
	}



	private function _generate_auth($api_key,$checkValue){
		if ($api_key == '')
			throw new Exception('your Api Key is empty');

		$aes = new tx_shscoutnetwebservice_AES($api_key,"CBC",$this->iv);

		$auth = array(
			'sha1' => sha1($checkValue),
			'md5' => md5($checkValue),
			'time' => time(),
		);
		$auth = serialize($auth);

		// this is done since we use the same iv all the time
		$first_block = '';
		for ($i=0;$i<16;$i++) {
			$first_block .= chr(rand(0,255));
		}

		$auth = strtr(base64_encode($aes->encrypt($first_block.$auth)), '+/=', '-_~');
		return $auth;
	}

}

class ScoutnetException extends \Exception{}

class ScoutnetException_MissingConfVar extends ScoutnetException{
	public function __construct( $var ){
		parent::__construct( "Missing '$var'. Please Contact your Admin to enter a valid credentials for ScoutNet Connect. You can request them via <a href=\"mailto:scoutnetconnect@scoutnet.de\">scoutnetConnect@ScoutNet.de</a>." );
	}
}
