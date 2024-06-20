<?php
/**
 * Copyright (c) 2015-2024 Stefan (Mütze) Horst
 *
 * I don't have the time to read through all the licences to find out
 * what they exactly say. But it's simple. It's free for non-commercial
 * projects, but as soon as you make money with it, I want my share :-)
 * (License: Free for non-commercial use)
 *
 * Authors: Stefan (Mütze) Horst <muetze@scoutnet.de>
 */

namespace ScoutNet\Api\Helpers;

use ScoutNet\Api\Exceptions\ScoutNetException;

/**
 * JsonRPCClientHelper
 *
 * @method get_data_by_global_id(array|int|null $globalId, mixed $filter)
 * @method deleteObject(string $type, ?int $globalId, int $id, string $username, $auth)
 * @method setData(string $type,int $id, mixed $object, string $username, $auth)
 * @method checkPermission(string $type, ?int $globalId, string $username, $auth)
 * @method requestPermission(string $type, ?int $globalId, string $username, $auth)
 * @method test()
 */
class JsonRPCClientHelper
{
    public const ERROR_CODE_METHOD_NAME_NO_STRING = 1492673555;
    public const ERROR_CODE_PARAMS_NO_ARRAY = 1492673563;
    public const ERROR_CODE_UNABLE_TO_CONNECT = 1492679926;
    public const ERROR_CODE_RESPONSE_ID_WRONG = 1492673515;

    /**
     * Debug state
     *
     * @var bool
     */
    private bool $debugOutput;

    /**
     * @var string[]
     */
    private array $debugLog = [];

    /**
     * The server URL
     *
     * @var string
     */
    private string $url;

    /**
     * The request id
     *
     * @var int
     */
    private int $request_id;

    /**
     * If true, notifications are performed instead of requests
     *
     * @var bool
     */
    private bool $notification = false;

    /**
     * @var bool
     */
    private bool $useCurl = false;

    /**
     * @var string|null
     */
    private ?string $curlProxyServer = null;

    /**
     * @var string|null
     */
    private ?string $curlProxyTunnel = null;

    /**
     * @var string|null
     */
    private ?string $curlProxyUserPass = null;

    /**
     * Takes the connection parameters
     *
     * @param string $url
     * @param bool $debug
     */
    public function __construct(string $url, bool $debug = false)
    {
        // server URL
        $this->url = $url;
        // debug state
        $this->debugOutput = $debug;

        // message id
        $this->request_id = 1;
    }

    public function setDebug($debug): void
    {
        $this->debugOutput = $debug;
    }

    private function debugLog($msg): void
    {
        if ($this->debugOutput) {
            $this->debugLog[] = $msg;
        }
    }
    public function getDebugLog(): array
    {
        return $this->debugLog;
    }

    private function printDebugLog(): void
    {
        if ($this->debugOutput) {
            echo implode("\n", $this->getDebugLog());
        }
    }

    public function setUseCurl($curlProxyServer = null, $curlProxyTunnel = null, $curlProxyUserPass = null): void
    {
        $this->useCurl = true;

        $this->curlProxyServer = $curlProxyServer;
        $this->curlProxyTunnel = $curlProxyTunnel;
        $this->curlProxyUserPass = $curlProxyUserPass;
    }

    /**
     * Sets the notification state of the object. In this state, notifications are performed, instead of requests.
     *
     * @param bool $notification
     */
    public function setRPCNotification(bool $notification): void
    {
        $this->notification = $notification;
    }

    /**
     * Performs a jsonRCP request and gets the results as an array
     *
     * @param string $method
     * @param array $params
     *
     * @return array|bool
     * @throws ScoutNetException
     */
    public function __call(string $method, array $params)
    {
        // sets notification or request task
        if ($this->notification) {
            $currentId = null;
        } else {
            $currentId = $this->request_id;
            ++$this->request_id;
        }

        // prepares the request
        $request = [
            'method' => $method,
            'params' => $params,
            'id' => $currentId,
        ];
        $request = json_encode($request);
        $this->debugLog('***** Request *****' . "\n" . $request . "\n" . '***** End Of request *****' . "\n");

        if ($this->useCurl && extension_loaded('curl')) {
            // performs the HTTP POST by use of libcurl
            $options = [
                CURLOPT_URL		=> $this->url,
                CURLOPT_POST		=> true,
                CURLOPT_POSTFIELDS	=> $request,
                CURLOPT_HTTPHEADER	=> [ 'Content-Type: application/json' ],
                CURLINFO_HEADER_OUT	=> true,
                CURLOPT_RETURNTRANSFER	=> true,
                CURLOPT_SSL_VERIFYHOST 	=> false,
                CURLOPT_SSL_VERIFYPEER 	=> false,
                CURLOPT_FOLLOWLOCATION	=> true,
            ];
            $ch = curl_init();
            curl_setopt_array($ch, $options);

            if (isset($_COOKIE['XDEBUG_SESSION'])) {
                curl_setopt($ch, CURLOPT_COOKIE, 'XDEBUG_SESSION=' . urlencode($_COOKIE['XDEBUG_SESSION']));
            }

            if ($this->curlProxyServer !== null) {
                curl_setopt($ch, CURLOPT_PROXY, $this->curlProxyServer);

                if ($this->curlProxyTunnel !== null) {
                    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $this->curlProxyTunnel);
                }
                if ($this->curlProxyUserPass !== null) {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->curlProxyUserPass);
                }
            }

            $response = trim(curl_exec($ch));
            curl_close($ch);
        } else {
            // performs the HTTP POST via fopen
            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/json',
                    'content' => $request,
                ]];

            if (isset($_COOKIE['XDEBUG_SESSION'])) {
                $opts['http']['header'] .= "\r\nCookie: XDEBUG_SESSION=" . urlencode($_COOKIE['XDEBUG_SESSION']);
            }
            $context  = stream_context_create($opts);

            if ($fp = @fopen($this->url, 'rb', false, $context)) {
                $response = '';
                while ($row = fgets($fp)) {
                    $response .= trim($row) . "\n";
                }
            } else {
                throw new ScoutNetException('Unable to connect to ' . $this->url, self::ERROR_CODE_UNABLE_TO_CONNECT);
            }
        }

        $this->debugLog('***** Server response *****' . "\n" . $response . '***** End of server response *****' . "\n");
        $response = json_decode($response, true);

        $this->printDebugLog();

        // final checks and return
        if (!$this->notification) {
            // check
            if ((int)$response['id'] !== $currentId) {
                throw new ScoutNetException('Incorrect response id (request id: ' . $currentId . ', response id: ' . $response['id'] . ')', 1572203283);
            }
            if (isset($response['error'])) {
                if (is_array($response['error'])) {
                    throw new ScoutNetException('Request error: ' . $response['error']['message'] . ' (' . $response['error']['code'] . ')', 1572203301);
                }
                throw new ScoutNetException('Request error: ' . $response['error'], 1572203301);
            }

            return $response['result'];
        }

        return true;
    }
}
