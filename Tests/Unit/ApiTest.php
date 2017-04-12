<?php

namespace ScoutNet\Api\Tests;

use PHPUnit\Framework\TestCase;
use ScoutNet\Api\Helpers\JsonRPCClientHelper;
use \Exception;
use ScoutNet\Api\Helpers\AesHelper;
use ScoutNet\Api\Models\Permission;
use ScoutNet\Api\ScoutnetApi;

/**
 * @covers \ScoutNet\Api\ScoutnetApi
 */
final class ApiTest extends TestCase {
    const AES_KEY = "12345678901234567890123456789012";
    const AES_IV = "1234567890123456";

    const API_PROVIDER = "phpunit";
    const API_USER = "phpunit";
    const API_KEY = "12345678901234567890123456789012";

    const MOCKED_TIME_VALUE = 1;
    const MOCKED_RAND_VALUE = 4;

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

    /**
     * set correct Write Credentials, so we can test the wrong Credentials too
     */
    private function _setCorrectWriteCredentials() {
        $this->sn->set_scoutnet_connect_data('https://www.scoutnet.de/community/scoutnetConnect.html', self::API_PROVIDER, self::AES_KEY, self::AES_IV);
    }

    /**
     * Generates the Auth for a given time, provider, api key etc..
     * So we can check if broken api keys are correcly detected.
     *
     * @param int    $time        Time for the Auth
     * @param string $provider    Provider in the Auth
     * @param string $user        User in the Auth
     * @param string $api_key     Api Key in the Auth
     * @param bool   $broken_md5  bool, if we generate a broken md5 hash
     * @param bool   $broken_sha1 bool, if we generate a broken sha1 hash
     *
     * @return string AUTH
     */
    private function _generateAuth($time = self::MOCKED_TIME_VALUE, $provider = self::API_PROVIDER, $user = self::API_USER, $api_key = self::API_KEY, $broken_md5 = false, $broken_sha1 = false) {
        $aes = new AesHelper(self::AES_KEY, "CBC", self::AES_IV);

        $content = [];
        $content['time'] = $time;
        $content['your_domain'] = $provider;
        $content['user'] = $user;
        $content['api_key'] = $api_key;

        // generate temp json to build hashes from
        $json = json_encode($content);

        $content['md5'] = $broken_md5 ? "12345678901234567890123456789012" : md5($json);
        $content['sha1'] = $broken_sha1 ? "1234567890123456789012345678901234567890" : sha1($json);

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

        $events = $this->sn->get_events_for_global_id_with_filter(4, array('limit' => '1', 'before' => '12.01.2012'));

        $this->assertEquals(1, count($events), "got more than one event");
        $this->assertEquals(4, $events[0]->getStructure()->getUid(), "wrong kalender id for event");
    }

    public function testGetKalenderElements() {
        $kalender = $this->sn->get_kalender_by_global_id('4');
        $this->assertEquals(1, count($kalender), "got more than one kalender");
        $this->assertEquals(4, $kalender[0]->getUid(), "wrong kalender id returned");
    }

    public function testGetIndexElements() {
        $indexes = $this->sn->get_index_for_global_id_with_filter(4, array('deeps' => '2'));

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

    public function testScoutNetConnectLogin() {
        $this->_setCorrectWriteCredentials();

        $connect_button = $this->sn->get_scoutnet_connect_login_button('http://localhost/testclient.php', true, 'https://www.scoutnet.de/images/scoutnetConnect.png', 'de');
        $expected_connect_button = file_get_contents(CACHE_DIR . "ConnectButton.html");


        $this->assertEquals($expected_connect_button, $connect_button);
    }

    /**
     * @expectedException \ScoutNet\Api\ScoutnetException_MissingConfVar
     * @expectedExceptionCode 1491938183
     */
    public function testGenerateAuthWithoutApiKey() {
        // call to private function
        $objectReflection = new \ReflectionObject($this->sn);
        $method = $objectReflection->getMethod('_generate_auth');
        $method->setAccessible(true);

        $method->invokeArgs($this->sn, ['test', 'test']);
    }

    public function testGenerateAuthWithCorrectApiKey() {
        // call to private function
        $objectReflection = new \ReflectionObject($this->sn);
        $method = $objectReflection->getMethod('_generate_auth');
        $method->setAccessible(true);

        $auth = $method->invokeArgs($this->sn, [self::API_KEY, 'test']);

        $this->assertEquals("cGJcjkxp40dKZP6Cf8QfpCiqDcXTRmrD50zdjtjBATWDeSMbj0Ro0etFtMJBASd-NBn41PC-y6IvI-h2QejUNm7g9IVpaSJj1_ibUSDoSwVNTdS_c0RSem8XyO-gTrl78gVH0AnJ13B9PUDj_mMsuhmTe_YWh-DuQ-x4ZTlM3IQ~", $auth);
    }

    public function testGetApiKeyFromDataWithNoAuth() {
        $this->_setCorrectWriteCredentials();

        $data = $this->sn->getApiKeyFromData();

        $this->assertEquals(false, $data);
    }

    /**
     * @expectedException \ScoutNet\Api\ScoutnetException
     * @expectedExceptionMessageRegExp /^AUTH is empty$/
     */
    public function testGetApiKeyFromDataWithEmptyAuth() {
        $this->_setCorrectWriteCredentials();

        // we mocked the time for the API
        $_GET['auth'] = 'AAAA';
        $this->sn->getApiKeyFromData();
    }

    /**
     * @expectedException \ScoutNet\Api\ScoutnetException
     * @expectedExceptionMessageRegExp /^Could not verify AUTH$/
     */
    public function testGetApiKeyFromDataWithBrokenMd5() {
        $this->_setCorrectWriteCredentials();

        // we mocked the time for the API
        $_GET['auth'] = $this->_generateAuth(self::MOCKED_TIME_VALUE, self::API_PROVIDER, self::API_USER, self::API_KEY, true, false);
        $this->sn->getApiKeyFromData();
    }

    /**
     * @expectedException \ScoutNet\Api\ScoutnetException
     * @expectedExceptionMessageRegExp /^Could not verify AUTH$/
     */
    public function testGetApiKeyFromDataWithBrokenSha1() {
        $this->_setCorrectWriteCredentials();

        // we mocked the time for the API
        $_GET['auth'] = $this->_generateAuth(self::MOCKED_TIME_VALUE, self::API_PROVIDER, self::API_USER, self::API_KEY, false, true);
        $this->sn->getApiKeyFromData();
    }

    /**
     * @expectedException \ScoutNet\Api\ScoutnetException
     * @expectedExceptionMessageRegExp /^AUTH is too old$/
     */
    public function testGetApiKeyFromDataWithExpiredTime() {
        $this->_setCorrectWriteCredentials();

        // we mocked the time for the API
        $_GET['auth'] = $this->_generateAuth(\ScoutNet\Api\time() - 5000);
        $this->sn->getApiKeyFromData();
    }

    /**
     * @expectedException \ScoutNet\Api\ScoutnetException
     * @expectedExceptionMessageRegExp /^AUTH for wrong provider$/
     */
    public function testGetApiKeyFromDataWithWrongProvider() {
        $this->_setCorrectWriteCredentials();

        // we mocked the time for the API
        $_GET['auth'] = $this->_generateAuth(self::MOCKED_TIME_VALUE, 'wrongProvider');
        $this->sn->getApiKeyFromData();
    }

    public function testGetApiKeyFromData() {
        $this->_setCorrectWriteCredentials();

        // we mocked the time for the API
        $_GET['auth'] = $this->_generateAuth();

        // this function gets the data from $_GET['auth']
        $data = $this->sn->getApiKeyFromData();

        $scoutnetUser = $data['user'];
        $api_key = $data['api_key'];

        $this->assertEquals(self::API_USER, $scoutnetUser);
        $this->assertEquals(self::API_KEY, $api_key);
    }

    public function testGetApiKeyFromDataCheckCache() {
        $this->_setCorrectWriteCredentials();

        // we mocked the time for the API
        $_GET['auth'] = $this->_generateAuth();

        $first_run = $this->sn->getApiKeyFromData();
        $second_run = $this->sn->getApiKeyFromData();

        $this->assertEquals($first_run, $second_run);
    }

    public function testWritePermissions() {
        $rights = $this->sn->has_write_permission_to_calender(1, self::API_USER, self::API_KEY);

        $this->assertEquals(Permission::AUTH_WRITE_ALLOWED, $rights);
    }

    public function testWritePermissionsNoAuth() {
        $rights = $this->sn->has_write_permission_to_calender(2, self::API_USER, self::API_KEY);

        $this->assertEquals(Permission::AUTH_NO_RIGHT, $rights);
    }

    public function testWritePermissionsPending() {
        $rights = $this->sn->has_write_permission_to_calender(3, self::API_USER, self::API_KEY);

        $this->assertEquals(Permission::AUTH_REQUEST_PENDING, $rights);
    }

    public function testRequestPermissionWorking() {
        // ask for rights
        $ret = $this->sn->request_write_permissions_for_calender(1, self::API_USER, self::API_KEY);

        $this->assertEquals(Permission::AUTH_REQUESTED, $ret['code']);
    }

    public function testRequestPermissionAlreadyRequested() {
        // ask for rights
        $ret = $this->sn->request_write_permissions_for_calender(2, self::API_USER, self::API_KEY);

        $this->assertEquals(Permission::AUTH_REQUEST_PENDING, $ret['code']);
    }


    public function testCreateEvent() {
        $testEvent = Array(
            'ID' => -1, // id of event to update -1 for new event
            'SSID' => 1,
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

       // $testEvent = $this->sn->write_event(-1, $testEvent, self::API_USER, self::API_KEY);
        echo "event written. It has ID ".$testEvent['ID']."\n";
    }


}


/**
 * Mocks
 */

DEFINE('CACHE_DIR', dirname(dirname(__FILE__)) . "/Fixtures/");

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
    public function __call($method, $params) {
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

        $cache_file = $this->cache_dir . $method . ".json";
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
 * We mock time() and rand() so we have predictable values.
 */
namespace ScoutNet\Api;

/**
 * mock time
 *
 * @return int
 */
function time() {
    return \ScoutNet\Api\Tests\ApiTest::MOCKED_TIME_VALUE;
}

/**
 * mock random
 *
 * @return int
 */
function rand() {
    return \ScoutNet\Api\Tests\ApiTest::MOCKED_RAND_VALUE;
}

/*

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
