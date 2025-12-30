<?php
/**
 * Copyright (c) 2017-2024 Stefan (Mütze) Horst
 *
 * I don't have the time to read through all the licences to find out
 * what they exactly say. But it's simple. It's free for non-commercial
 * projects, but as soon as you make money with it, I want my share :-)
 * (License: Free for non-commercial use)
 *
 * Authors: Stefan (Mütze) Horst <muetze@scoutnet.de>
 */

namespace ScoutNet\Api;

use Exception;
use JsonException;
use ScoutNet\Api\Exceptions\ScoutNetException;
use ScoutNet\Api\Exceptions\ScoutNetExceptionMissingConfVar;
use ScoutNet\Api\Helpers\AesHelper;
use ScoutNet\Api\Helpers\CacheHelper;
use ScoutNet\Api\Helpers\ConverterHelper;
use ScoutNet\Api\Helpers\JsonRPCClientHelper;
use ScoutNet\Api\Model\Category;
use ScoutNet\Api\Model\Event;
use ScoutNet\Api\Model\Index;
use ScoutNet\Api\Model\Permission;
use ScoutNet\Api\Model\Structure;

class ScoutnetApi
{
    public const UNSECURE_START_IV = '1234567890123456';

    public const ERROR_AUTH_BROKEN = 1491935269;
    public const ERROR_WRONG_PROVIDER = 1491935295;
    public const ERROR_AUTH_TOO_OLD = 1491935476;
    public const ERROR_AUTH_EMPTY = 1491935505;

    public const ERROR_MISSING_API_KEY = 1491938183;
    public const ERROR_MISSING_API_USER = 1492695814;

    public JsonRPCClientHelper $SN;

    public $snData;

    private const SN_CONNECT_IMAGE_URL  = 'https://www.scoutnet.de/fileadmin/user_upload/scoutnetConnect.png';
    private const SN_CONNECT_LOGIN_URL  = 'https://www.scoutnet.de/community/scoutnetConnect';
    private const SN_JSONRPC_SERVER_URL = 'https://www.scoutnet.de/jsonrpc/server.php';

    private string $provider;
    private string $aes_key;
    private string $aes_iv;

    private $api_user;
    private $api_key;

    private string $login_url;

    private CacheHelper $cache;
    private ConverterHelper $converter;

    /**
     * Construct the ScoutNet API. To read you need not set anything. For Writing access you must set the
     * provider, aes_key and aes_iv values
     *
     * @param string $api_url if not set use the default value
     * @param string $login_url use default value
     * @param string $provider set the provider name to write content
     * @param string $aes_key set aes key for the provider
     * @param string $aes_iv set aes iv for the provider
     *
     * @throws ScoutNetExceptionMissingConfVar
     */
    public function __construct(string $api_url = self::SN_JSONRPC_SERVER_URL, string $login_url = self::SN_CONNECT_LOGIN_URL, string $provider = '', string $aes_key = '', string $aes_iv = '')
    {
        $this->SN = new JsonRPCClientHelper($api_url);

        $this->set_scoutnet_connect_data($login_url, $provider, $aes_key, $aes_iv);

        // we hold reference, so we can use it later
        $this->cache = new CacheHelper();
        $this->converter = new ConverterHelper($this->cache);
    }

    /**
     * Use this function to set the Parameter to be able to use the write API (if not set in the constructor)
     *
     * @param string $login_url
     * @param string $provider
     * @param string $aes_key
     * @param string $aes_iv
     *
     * @throws ScoutNetExceptionMissingConfVar
     */
    public function set_scoutnet_connect_data(string $login_url = '', string $provider = '', string $aes_key = '', string $aes_iv = ''): void
    {
        if ($login_url === '') {
            $login_url = self::SN_CONNECT_LOGIN_URL;
        }
        $provider = trim($provider);

        $this->login_url = $login_url;
        $this->provider = $provider;
        $this->aes_key = $aes_key;
        $this->aes_iv = $aes_iv;

        // if one is set all must be set correct
        if ($provider !== '' || $aes_key !== '' || $aes_iv !== '') {
            $this->_check_for_all_configValues();
        }
    }

    /**
     * checks if ScoutNet connect Values are set correctly
     *
     * @throws ScoutNetExceptionMissingConfVar
     */
    private function _check_for_all_configValues(): void
    {
        if (trim($this->aes_key) === '' || strlen($this->aes_key) !== 32) {
            throw new ScoutNetExceptionMissingConfVar('aes_key');
        }
        if (trim($this->aes_iv) === '' || strlen($this->aes_iv) !== 16) {
            throw new ScoutNetExceptionMissingConfVar('aes_iv');
        }
        if (trim($this->login_url) === '') {
            throw new ScoutNetExceptionMissingConfVar('login_url');
        }
        if (trim($this->provider) === '') {
            throw new ScoutNetExceptionMissingConfVar('provider');
        }
    }

    public function loginUser($api_user, $api_key): void
    {
        $this->api_user = $api_user;
        $this->api_key = $api_key;
    }

    /**
     * @throws ScoutNetExceptionMissingConfVar
     */
    private function _check_login(): void
    {
        if (trim($this->api_user) === '') {
            throw new ScoutNetExceptionMissingConfVar('api_user', self::ERROR_MISSING_API_USER);
        }
        if (trim($this->api_key) === '' || strlen($this->api_key) !== 32) {
            throw new ScoutNetExceptionMissingConfVar('api_key', self::ERROR_MISSING_API_KEY);
        }
    }

    /**
     * @param int|int[] $ids   SSIDs of Structures to request data from
     * @param array $query Filter Query for Request
     *
     * @return mixed
     */
    protected function load_data_from_scoutnet(array|int $ids, array $query): mixed
    {
        return $this->SN->get_data_by_global_id($ids, $query);
    }

    /**
     * Load Events for Structure
     *
     * @param int|int[] $ids SSIDs of Kalenders to request Events from
     * @param array $filter only find Events matching this filter
     *
     * @return  Event[]
     * @throws Exception
     */
    public function get_events_for_global_id_with_filter(array|int $ids, array $filter): array
    {
        $events = [];

        foreach ($this->load_data_from_scoutnet($ids, ['events' => $filter]) as $record) {
            if ($record['type'] === 'user') {
                // convert to User and save to cache
                $this->converter->convertApiToUser($record['content']);
            } elseif ($record['type'] === 'stufe') {
                // convert to Stufe and save to cache
                $this->converter->convertApiToSection($record['content']);
            } elseif ($record['type'] === 'kalender') {
                // convert to Structure and save to cache
                $this->converter->convertApiToStructure($record['content']);
            } elseif ($record['type'] === 'event') {
                $events[] = $this->converter->convertApiToEvent($record['content']);
            }
        }
        return $events;
    }

    /**
     * Load Index Elements for this Structure Element
     *
     * @param int|int[] $ids    SSIDs to find Index Elements for
     * @param array $filter Filter for Elements
     *
     * @return Index[]
     */
    public function get_index_for_global_id_with_filter(array|int $ids, array $filter): array
    {
        $indexes = [];
        foreach ($this->load_data_from_scoutnet($ids, ['index' => $filter]) as $record) {
            if ($record['type'] === 'index') {
                $index = $this->converter->convertApiToIndex($record['content']);

                $indexes[$index->getUid()] = $index;
            }
        }

        return $indexes;
    }

    /**
     * Load Events with event_ids from the given Kalenders
     *
     * @param int|int[] $ids SSIDs to load events for
     * @param int[] $event_ids IDs of Events to load
     *
     * @return Event[]
     * @throws Exception
     */
    public function get_events_with_ids(array|int $ids, array $event_ids): array
    {
        return $this->get_events_for_global_id_with_filter($ids, ['event_ids' => $event_ids]);
    }

    /**
     * Load Categories by ID
     *
     * @param int|int[] $ids       IDs of Categories
     *
     * @return Category[]
     * @api
     */
    public function get_categories_by_ids(array|int $ids): array
    {
        $categories = [];
        foreach ($this->load_data_from_scoutnet([], ['categories' => ['uid' => $ids]]) as $record) {
            if ($record['type'] === 'categorie') {
                $categories[] = $this->converter->convertApiToCategory($record['content']);
            }
        }
        return $categories;
    }

    /**
     * Load All Categories
     *
     * @return Category[]
     * @api
     */
    public function get_all_categories(): array
    {
        $categories = [];
        foreach ($this->load_data_from_scoutnet([], ['categories' => ['all' => true]]) as $record) {
            if ($record['type'] === 'categorie') {
                $categories[] = $this->converter->convertApiToCategory($record['content']);
            }
        }
        return $categories;
    }

    /**
     * Load Categories by ID
     *
     * @param int $ssid
     * @param int $event_id
     *
     * @return Category[]
     * @api
     */
    public function get_all_categories_for_kalender_and_event(int $ssid, int $event_id): array
    {
        $categories = [];
        foreach ($this->load_data_from_scoutnet([$ssid], ['categories' => ['generatedCategoriesForEventId' => $event_id]]) as $record) {
            if ($record['type'] === 'categorie') {
                $categories[] = $this->converter->convertApiToCategory($record['content']);
            }
        }
        return $categories;
    }

    /**
     * @param int|int[] $ids SSIDs to load Kalenders for
     *
     * @return Structure[]
     */
    public function get_kalender_by_global_id(array|int $ids): array
    {
        $kalenders = [];
        foreach ($this->load_data_from_scoutnet($ids, ['kalenders' => []]) as $record) {
            if ($record['type'] === 'kalender') {
                $kalenders[] = $this->converter->convertApiToStructure($record['content']);
            }
        }

        return $kalenders;
    }

    /**
     * Use this function to write event to scoutnet
     *
     * @param Event $event Event Object
     *
     * @return Event
     * @throws Exception
     */
    public function write_event(Event $event): Event
    {
        $type = 'event';
        $id = $event->getUid();
        $apiData = $this->converter->convertEventToApi($event);

        return $this->converter->convertApiToEvent($this->write_object($type, $id, $apiData));
    }

    /**
     * @param string $type Type of Object to write; The API only works with events
     * @param int $id ID of object to update/ create
     * @param array $data Object data as Array
     *
     * @return mixed
     * @throws ScoutNetExceptionMissingConfVar
     */
    public function write_object(string $type, int $id, array $data): mixed
    {
        $auth = $this->_generate_auth($type . $id . serialize($data) . $this->api_user);

        return $this->SN->setData($type, $id, $data, $this->api_user, $auth);
    }

    /**
     * @throws ScoutNetExceptionMissingConfVar
     */
    public function delete_event(int $ssid, int $id)
    {
        $type = 'events';
        $auth = $this->_generate_auth($type . $ssid . $id . $this->api_user);

        return $this->SN->deleteObject($type, $ssid, $id, $this->api_user, $auth);
    }

    /**
     * This Function returns if a given user has write permissions on a specified Kalendar.
     *
     * @param int $ssid SSID of Calendar
     *
     * @return int State of Write Rights
     * @throws ScoutNetExceptionMissingConfVar
     */
    public function has_write_permission_to_calender(int $ssid): int
    {
        return $this->get_permissions_of_type_for_structure('event', $ssid)->getState();
    }

    /**
     * @param string $type Type of Permissions
     * @param int $ssid SSID of Structure
     *
     * @return Permission
     * @throws ScoutNetExceptionMissingConfVar
     */
    public function get_permissions_of_type_for_structure(string $type, int $ssid): Permission
    {
        $auth = $this->_generate_auth($type . $ssid . $this->api_user);

        $right = $this->SN->checkPermission($type, $ssid, $this->api_user, $auth);
        $right['type'] = $type;

        return $this->converter->convertApiToPermission($right);
    }

    /**
     * Request Write Permission for a specified User
     *
     * @param int $ssid    SSID of Structure
     *
     * @return mixed
     */
    public function request_write_permissions_for_calender(int $ssid): mixed
    {
        $type = 'event';

        return $this->request_permissions_of_type_for_structure($type, $ssid);
    }

    /**
     * Request Permission of given type for a specified User
     *
     * @param string $type Type of Permission
     * @param int $ssid SSID of Structure
     *
     * @return mixed
     * @throws ScoutNetExceptionMissingConfVar
     */
    public function request_permissions_of_type_for_structure(string $type, int $ssid): mixed
    {
        $auth = $this->_generate_auth($type . $ssid . $this->api_user);

        return $this->SN->requestPermission($type, $ssid, $this->api_user, $auth);
    }

    /**
     * returns the ScoutNet Connect Button in formated HTML
     *
     * @param string $returnUrl
     * @param bool $requestApiKey
     * @param string $imageURL
     * @param string $lang
     *
     * @return string
     * @throws ScoutNetExceptionMissingConfVar
     */
    public function get_scoutnet_connect_login_button(string $returnUrl = '', bool $requestApiKey = false, string $imageURL = self::SN_CONNECT_IMAGE_URL, string $lang = 'de'): string
    {
        $this->_check_for_all_configValues();
        $button = '<form action="' . $this->login_url . '" id="scoutnetLogin" method="get" target="_self">' . "\n";

        $button .= $returnUrl === '' ? '' : '    ' . '<input type="hidden" name="redirect_url" value="' . $returnUrl . '" />' . "\n";
        $button .= '    ' . '<input type="hidden" name="lang" value="' . $lang . '"/>' . "\n";
        $button .= '    ' . '<input type="hidden" name="provider" value="' . $this->provider . '" />' . "\n";
        $button .= $requestApiKey ? ('    ' . '<input type="hidden" name="createApiKey" value="1" />' . "\n") : '';

        $button .= '    ' . '<a href="#" onclick="document.getElementById(\'scoutnetLogin\').submit(); return false;">' . "\n";

        $button .= '        ' . '<img src="' . $imageURL . '" title="Login with Scoutnet" alt="scoutnet Login"/>' . "\n";
        $button .= '    ' . '</a>' . "\n";

        $button .= '</form>';

        return $button;
    }

    /**
     * Extract the Api key from _GET variables. This Function is used in after the ScoutNet Connect Login.
     *
     * @return string[]|bool
     * @throws ScoutNetException
     * @throws JsonException
     */
    public function getApiKeyFromData(): array|bool
    {
        // we already extracted the Data so do not do it twice
        if (isset($this->snData)) {
            return $this->snData;
        }

        // there is no _GET['auth'] we cannot extract it
        if (!isset($_GET['auth'])) {
            return false;
        }

        // decode Input
        $base64 = base64_decode(strtr($_GET['auth'], '-_~', '+/='));

        if (trim($base64) === '') {
            throw new ScoutnetException('AUTH is empty', self::ERROR_AUTH_EMPTY);
        }

        // check if we have the appropriate values configured (AES_Key and AES_IV etc.)
        $this->_check_for_all_configValues();

        // Use AES with CBC and the set IV and KEY
        $aes = new AesHelper($this->aes_key, 'CBC', $this->aes_iv);

        // decrypt data and drop first Block, since it only contains Random to fix static IV
        $json = substr($aes->decrypt($base64), strlen($this->aes_iv));
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if ($data === '') {
            throw new ScoutnetException('Could not verify AUTH', self::ERROR_AUTH_BROKEN);
        }

        $md5 = $data['md5'];
        unset($data['md5']);
        $sha1 = $data['sha1'];
        unset($data['sha1']);

        // the hashes are generated without the hashes themselves
        if (md5(json_encode($data, JSON_THROW_ON_ERROR)) !== $md5) {
            throw new ScoutnetException('Could not verify AUTH', self::ERROR_AUTH_BROKEN);
        }

        if (sha1(json_encode($data, JSON_THROW_ON_ERROR)) !== $sha1) {
            throw new ScoutnetException('Could not verify AUTH', self::ERROR_AUTH_BROKEN);
        }

        if (time() - $data['time'] > 3600) {
            throw new ScoutnetException('AUTH is too old', self::ERROR_AUTH_TOO_OLD);
        }

        $your_domain = $this->provider;

        if ($data['your_domain'] !== $your_domain) {
            throw new ScoutnetException('AUTH for wrong provider', self::ERROR_WRONG_PROVIDER);
        }

        $this->snData = $data;

        return $data;
    }

    /**
     * This function generates the Auth hash to verify request
     *
     * @param string $checkValue Value to sign with Api Key
     *
     * @return string
     * @throws ScoutNetExceptionMissingConfVar
     * @throws JsonException
     */
    private function _generate_auth(string $checkValue): string
    {
        $this->_check_login();

        $aes = new AesHelper($this->api_key, 'CBC', self::UNSECURE_START_IV);

        $auth = [
            'sha1' => sha1($checkValue),
            'md5' => md5($checkValue),
            'time' => time(),
        ];
        $auth = json_encode($auth, JSON_THROW_ON_ERROR);

        // Generate random first block, which is deleted on the reciever side.
        // This is done since we use the same iv all the time.
        $first_block = '';
        for ($i = 0; $i < 16; $i++) {
            $first_block .= chr(rand(0, 255));
        }

        return strtr(base64_encode($aes->encrypt($first_block . $auth)), '+/=', '-_~');
    }
}
