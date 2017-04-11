<?php
namespace ScoutNet\Api\Tests;

use PHPUnit\Framework\TestCase;
use ScoutNet\Api\Helpers\JsonRPCClientHelper;
use \Exception;
use ScoutNet\Api\Helpers\AesHelper;
use ScoutNet\Api\Models\Permission;
use ScoutNet\Api\ScoutnetApi;

DEFINE('CACHE_DIR', dirname(dirname(__FILE__))."/Fixtures/");

class JsonRPCClientHelperMock extends JsonRPCClientHelper {
    private $cache_dir = null;

    public function __construct($url, $debug = false, $cache_dir) {
        parent::__construct($url, $debug);
        $this->cache_dir = $cache_dir;
    }
    /**
     * Performs a jsonRCP request and gets the results as an array
     *
     * @param string $method
     * @param array  $params
     *
     * @return array|bool
     * @throws \Exception
     */
    public function __call($method,$params) {
        // check
        if (!is_scalar($method)) {
            throw new Exception('Method name has no scalar value');
        }

        // check
        if (is_array($params)) {
            // no keys
            $params = array_values($params);
        } else {
            throw new Exception('Params must be given as array');
        }

        $cache_file = $this->cache_dir.$method.".json";
        $cache = [];
        if (is_file($cache_file)) {
            $cache = json_decode(file_get_contents($cache_file), true);
        }

        $param_json = json_encode($params);

        if (!isset($cache[$param_json])) {
            $cache[$param_json] = parent::__call($method, $params);
            file_put_contents($cache_file, json_encode($cache));
        }

        return $cache[$param_json];
    }

}

/**
 * @covers \ScoutNet\Api\ScoutnetApi
 */
final class ApiTest extends TestCase {
    const AES_KEY = "12345678901234567890123456789012";
    const AES_IV = "1234567890123456";

    const API_PROVIDER = "phpunit";
    const API_USER = "phpunit";
    const API_KEY = "12345678901234567890123456789012";

    private $sn = null;

    public function __construct($name = null, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->sn = new ScoutnetApi();
        $jsonRPCClientHelperMock = new JsonRPCClientHelperMock('https://www.scoutnet.de/jsonrpc/server.php', false, CACHE_DIR); // load data to be mocked from $url

        // inject RPCClientMock
        $objectReflection = new \ReflectionObject($this->sn);
        $property = $objectReflection->getProperty('SN');
        $property->setAccessible(true);
        $property->setValue($this->sn, $jsonRPCClientHelperMock);
    }

    private function _setCorrectWriteCredentials() {
        $this->sn->set_scoutnet_connect_data('https://www.scoutnet.de/community/scoutnetConnect.html',self::API_PROVIDER, self::AES_KEY, self::AES_IV);
    }

    private function _generateExpectedApiKey()
    {
        $aes = new AesHelper(self::AES_KEY, "CBC", self::AES_IV);

        $content = ['test' => 'bla'];
        $content['time'] = time();
        $content['your_domain'] = self::API_PROVIDER;
        $content['user'] = self::API_USER;
        $content['api_key'] = self::API_KEY;

        // generate temp json to build hashes from
        $json = json_encode($content);

        $content['md5'] = md5($json);
        $content['sha1'] = sha1($json);

        $json = json_encode($content);

        $data = base64_encode(self::AES_IV . $aes->encrypt($json));

        return $data;
    }
	public function testCanBeCreated() {
		$this->assertInstanceOf(
			ScoutnetApi::class,
			new ScoutnetApi()
		);
	}

	public function testGetEventsForGlobalId() {

        $events = $this->sn->get_events_for_global_id_with_filter('4',array('limit'=>'1','before'=>'12.01.2012'));

        $this->assertEquals(1, count($events),"got more than one event");
        $this->assertEquals(4, $events[0]->Kalender->ID, "wrong kalender id for event");
    }

    public function testGetKalenderElements() {
        $kalender = $this->sn->get_kalender_by_global_id('4');
        $this->assertEquals(1, count($kalender),"got more than one kalender");
        $this->assertEquals(4, $kalender[0]->ID, "wrong kalender id returned");
    }

    public function testGetIndexElements() {
        $indexes = $this->sn->get_index_for_global_id_with_filter('4',array('deeps'=>'2'));

        $this->assertGreaterThan(13, count($indexes), 'to few structures returned, as of writing there were 113');

        $diozese = $indexes['4']; // diozese Köln
        $this->assertGreaterThan(5, count($diozese->getChildren()), 'less than 5 Bezirke, as of writing there were 12');

        $bezirk = $indexes['17']; // bezirk erft
        $this->assertGreaterThan(5, count($bezirk->getChildren()), "less than 5 Stämme, as of writing there were 12");
    }

    /**
     * @expectedException \ScoutNet\Api\ScoutnetException_MissingConfVar
     * @expectedExceptionMessageRegExp /^Missing 'aes_key'.*$/
     */
    public function testSetScoutnetConnectDataFailureAES_KEY() {
        $this->sn->set_scoutnet_connect_data('https://www.scoutnet.de/community/scoutnetConnect.html', self::API_PROVIDER, '', self::AES_IV);
    }

    /**
     * @expectedException \ScoutNet\Api\ScoutnetException_MissingConfVar
     * @expectedExceptionMessageRegExp /^Missing 'aes_iv'.*$/
     */
    public function testSetScoutnetConnectDataFailureAES_IV() {
        $this->sn->set_scoutnet_connect_data('https://www.scoutnet.de/community/scoutnetConnect.html', self::API_PROVIDER, self::AES_KEY, '');
    }

    /**
     * @expectedException \ScoutNet\Api\ScoutnetException_MissingConfVar
     * @expectedExceptionMessageRegExp /^Missing 'provider'.*$/
     */
    public function testSetScoutnetConnectDataFailiureProvider() {
        $this->sn->set_scoutnet_connect_data('https://www.scoutnet.de/community/scoutnetConnect.html', '', self::AES_KEY, self::AES_IV);
    }

    /**
     * @expectedException \ScoutNet\Api\ScoutnetException_MissingConfVar
     * @expectedExceptionMessageRegExp /^Missing 'login_url'.*$/
     */
    public function testSetScoutnetConnectDataFailiureLoginurl() {
        $this->sn->set_scoutnet_connect_data('  ', self::API_PROVIDER, self::AES_KEY, self::AES_IV);
    }

    public function testScoutNetConnectLogin(){
        $this->_setCorrectWriteCredentials();

        $connect_button = $this->sn->get_scoutnet_connect_login_button('http://localhost/testclient.php', true, 'https://www.scoutnet.de/images/scoutnetConnect.png', 'de');
        $expected_connect_button = file_get_contents(CACHE_DIR."ConnectButton.html");


        $this->assertEquals($expected_connect_button, $connect_button);
    }

    public function testGetApiKeyFromData() {
        $this->_setCorrectWriteCredentials();

        $_GET['auth'] = $this->_generateExpectedApiKey();

        // this function gets the data from $_GET['auth']
        $data = $this->sn->getApiKeyFromData();

        $scoutnetUser = $data['user'];
        $api_key = $data['api_key'];

        $this->assertEquals(self::API_USER, $scoutnetUser);
        $this->assertEquals(self::API_KEY, $api_key);
    }

    public function testWritePermissions(){
        $rights = $this->sn->has_write_permission_to_calender(4, self::API_USER, self::API_KEY);

        $this->assertEquals(Permission::AUTH_WRITE_ALLOWED, $rights);
    }
}

namespace ScoutNet\Api;

/**
 * mock time
 *
 * @return int
 */
function time() {
    return 1;
}

/**
 * mock random
 *
 * @return int
 */
function rand() {
    return 4;
}

/*

// Save this data here

echo '---------- write check user rights -----------------'."\n";

// this is connected to the aes key!
$ssid = 17;

// check if we have right to edit our kalender
$rights = $sn->has_write_permission_to_calender($ssid, $scoutnetUser, $api_key);

// code == 0 -> user has access
// code == 1 -> user has no access

if ($rights['code'] !== 0) {
	echo "you do not have permission to access this calender. We ask for rights.\n";
	// ask for rights
	$sn->request_write_permissions_for_calender($ssid, $scoutnetUser, $api_key);

	// this sends an email to the admin and asks for rights
} else {
	echo "you can access the kalender.\n";
}

echo '---------- write get Kalender Meta tags -----------------'."\n";

$kalenders = $sn->get_kalender_by_global_id(array($ssid));
// only use first
$kalender = $kalenders[0];

// this contains all used and forced kategories
//print_r($kalender);

echo '---------- write create event -----------------'."\n";

$testEvent = Array(
	'ID' => -1, // id of event to update -1 for new event
	'SSID' => $ssid,
	'Title' => 'F+F Mitgliederversammlung',
	'Organizer' => 'Freundes- und Förderkreis',
	'Target_Group' => 'Freunde',
	'Start' => 1354294800,
	'End' => 1354294800,
	'All_Day' => false,
	'ZIP' => '',
	'Location' => 'Tagungs- und Gästehaus Rolandstr.',
	'URL_Text' => '',
	'URL' => '',
	'Description' => '',
	'Stufen' => Array (),
	'Keywords' => Array (
		'193' => 1,
		'543' => 1,
	)
);

$testEvent = $sn->write_event($testEvent['ID'], $testEvent, $scoutnetUser, $api_key);
echo "event written. It has ID ".$testEvent['ID']."\n";

echo '---------- write delete event -----------------'."\n";
$sn->delete_event($ssid, $testEvent['ID'], $scoutnetUser, $api_key);
echo 'event deleted.'."\n";
*/
