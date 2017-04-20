<?php
namespace ScoutNet\Api\Helpers;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 Stefan "Mütze" Horst <muetze@scoutnet.de>, ScoutNet
 *
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

use Exception;

/**
 * JsonRPCClientHelper
 *
 * @method get_data_by_global_id($globalid, $filter)
 * @method deleteObject($type, $globalid, $id, $username, $auth)
 * @method setData($type,$id,$object,$username,$auth)
 * @method checkPermission($type,$globalid,$username,$auth)
 * @method requestPermission($type,$globalid,$username,$auth)
 */
class JsonRPCClientHelper {
    const ERROR_CODE_METHOD_NAME_NO_STRING = 1492673555;
    const ERROR_CODE_PARAMS_NO_ARRAY = 1492673563;
    const ERROR_CODE_UNABLE_TO_CONNECT = 1492679926;
    const ERROR_CODE_RESPONSE_ID_WRONG = 1492673515;
	
	/**
	 * Debug state
	 *
	 * @var boolean
	 */
	private $debug;

    /**
     * @var string[]
     */
	private $debugLog = [];
	
	/**
	 * The server URL
	 *
	 * @var string
	 */
	private $url;

	/**
	 * The request id
	 *
	 * @var integer
	 */
	private $id;

	/**
	 * If true, notifications are performed instead of requests
	 *
	 * @var boolean
	 */
	private $notification = false;

    /**
     * @var bool
     */
	private $useCurl = false;

    /**
     * @var string
     */
	private $curlProxyServer = null;

    /**
     * @var string
     */
	private $curlProxyTunnel = null;

    /**
     * @var string
     */
	private $curlProxyUserPass = null;

	/**
	 * Takes the connection parameters
	 *
	 * @param string $url
	 * @param boolean $debug
	 */
    public function __construct($url, $debug = false) {
		// server URL
		$this->url = $url;
		// debug state
        $this->debug = $debug;

		// message id
		$this->id = 1;
	}

	public function setDebug($debug) {
        $this->debug = $debug;
    }

	private function debugLog($msg) {
	    if ($this->debug) {
            $this->debugLog[] = $msg;
        }
    }
    public function getDebugLog() {
        return $this->debugLog;
    }

    private function printDebugLog() {
	    if ($this->debug) {
            echo implode("\n", $this->getDebugLog());
        }
    }

	public function setUseCurl($curlProxyServer = null, $curlProxyTunnel = null, $curlProxyUserPass = null){
		$this->useCurl = true;

		$this->curlProxyServer = $curlProxyServer;
		$this->curlProxyTunnel = $curlProxyTunnel;
		$this->curlProxyUserPass = $curlProxyUserPass;
	}
	
	/**
	 * Sets the notification state of the object. In this state, notifications are performed, instead of requests.
	 *
	 * @param boolean $notification
	 */
	public function setRPCNotification($notification) {
	    $this->notification = $notification;
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
    public function __call($method, $params) {
		// check if method is string
		if (!is_scalar($method)) {
			throw new Exception('Method name has no scalar value', self::ERROR_CODE_METHOD_NAME_NO_STRING);
		}
		
		// check if params are Array
		if (is_array($params)) {
			// drop the keys
			$params = array_values($params);
		} else {
			throw new Exception('Params must be given as array', self::ERROR_CODE_PARAMS_NO_ARRAY);
		}
		
		// sets notification or request task
		if ($this->notification) {
			$currentId = NULL;
		} else {
			$currentId = $this->id;
		}
		
		// prepares the request
		$request = array(
			'method' => $method,
			'params' => $params,
			'id' => $currentId
		);
		$request = json_encode($request);
		$this->debugLog('***** Request *****'."\n".$request."\n".'***** End Of request *****'."\n");


		if ($this->useCurl && extension_loaded('curl')) {
			// performs the HTTP POST by use of libcurl
			$options = array(
				CURLOPT_URL		=> $this->url,
				CURLOPT_POST		=> true,
				CURLOPT_POSTFIELDS	=> $request,
				CURLOPT_HTTPHEADER	=> array( 'Content-Type: application/json' ),
				CURLINFO_HEADER_OUT	=> true,
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_SSL_VERIFYHOST 	=> false,
				CURLOPT_SSL_VERIFYPEER 	=> false,
				CURLOPT_FOLLOWLOCATION	=> true,
			);
			$ch = curl_init();
			curl_setopt_array( $ch, $options );

			if (isset($_COOKIE['XDEBUG_SESSION'])) {
				curl_setopt($ch, CURLOPT_COOKIE, 'XDEBUG_SESSION='.urlencode($_COOKIE['XDEBUG_SESSION']));
			}

			if ($this->curlProxyServer != null) {
				curl_setopt($ch, CURLOPT_PROXY, $this->curlProxyServer);

				if ($this->curlProxyTunnel != null) {
					curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $this->curlProxyTunnel);
				}
				if ($this->curlProxyUserPass != null)	{
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->curlProxyUserPass);
				}
			}

			$response = trim( curl_exec( $ch ) );
			curl_close( $ch );
		} else {
			// performs the HTTP POST via fopen
			$opts = array (
				'http' => [
					'method'  => 'POST',
					'header'  => "Content-type: application/json",
					'content' => $request
                ]);

            if (isset($_COOKIE['XDEBUG_SESSION'])) {
                $opts['http']['header'] .= "\r\nCookie: XDEBUG_SESSION=".urlencode($_COOKIE['XDEBUG_SESSION']);
            }
			$context  = stream_context_create($opts);

			if ($fp = @fopen($this->url, 'r', false, $context)) {
				$response = '';
				while($row = fgets($fp)) {
					$response .= trim($row)."\n";
				}
			} else {
				throw new Exception('Unable to connect to '.$this->url, self::ERROR_CODE_UNABLE_TO_CONNECT);
			}
		}

        $this->debugLog('***** Server response *****'."\n".$response.'***** End of server response *****'."\n");
        $response = json_decode( $response, true );

		$this->printDebugLog();

		// final checks and return
		if (!$this->notification) {
			// check
			if ($response['id'] != $currentId) {
				throw new Exception('Incorrect response id (request id: '.$currentId.', response id: '.$response['id'].')', self::ERROR_CODE_RESPONSE_ID_WRONG);
			}
			if (!is_null($response['error'])) {
				throw new Exception('Request error: '.$response['error']['message'], $response['error']['code']);
			}
			
			return $response['result'];
			
		} else {
			return true;
		}
	}
}
