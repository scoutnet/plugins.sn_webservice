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

namespace ScoutNet\Api\Tests\Unit;

use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use ScoutNet\Api\Exceptions\ScoutNetException;
use ScoutNet\Api\Exceptions\ScoutNetExceptionMissingConfVar;
use ScoutNet\Api\Helpers\AesHelper;
use ScoutNet\Api\Helpers\JsonRPCClientHelper;
use ScoutNet\Api\Model\Category;
use ScoutNet\Api\Model\Event;
use ScoutNet\Api\Model\Permission;
use ScoutNet\Api\Model\Structure;
use ScoutNet\Api\Model\User;
use ScoutNet\Api\ScoutnetApi;

final class ApiTest extends TestCase
{
    public const AES_KEY = '12345678901234567890123456789012';
    public const AES_IV = '1234567890123456';

    public const API_PROVIDER = 'phpunit';
    public const API_USER = 'phpunit';
    public const API_KEY = '12345678901234567890123456789012';

    public const MOCKED_TIME_VALUE = 1;
    public const MOCKED_RAND_VALUE = 4;

    private $sn;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->sn = new ScoutnetApi();
        $jsonRPCClientHelperMock = new JsonRPCClientHelperMock('https://www.scoutnet.de/jsonrpc/server.php', false, CACHE_DIR); // load data to be mocked from $url

        // inject RPCClientMock
        $objectReflection = new ReflectionObject($this->sn);
        $property = $objectReflection->getProperty('SN');
        $property->setAccessible(true);
        $property->setValue($this->sn, $jsonRPCClientHelperMock);
    }

    /**
     * set correct Write Credentials, so we can test the wrong Credentials too
     */
    private function _setCorrectWriteCredentials()
    {
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
    private function _generateAuth($time = self::MOCKED_TIME_VALUE, $provider = self::API_PROVIDER, $user = self::API_USER, $api_key = self::API_KEY, $broken_md5 = false, $broken_sha1 = false)
    {
        $aes = new AesHelper(self::AES_KEY, 'CBC', self::AES_IV);

        $content = [];
        $content['time'] = $time;
        $content['your_domain'] = $provider;
        $content['user'] = $user;
        $content['api_key'] = $api_key;

        // generate temp json to build hashes from
        $json = json_encode($content);

        $content['md5'] = $broken_md5 ? '12345678901234567890123456789012' : md5($json);
        $content['sha1'] = $broken_sha1 ? '1234567890123456789012345678901234567890' : sha1($json);

        $json = json_encode($content);

        $data = base64_encode(self::AES_IV . $aes->encrypt($json));

        return $data;
    }

    public function testCanBeCreated()
    {
        self::assertInstanceOf(
            ScoutnetApi::class,
            new ScoutnetApi()
        );
    }

    public function testGetEventsForGlobalId(): void
    {
        $events = $this->sn->get_events_for_global_id_with_filter(4, ['limit' => '1', 'before' => '12.01.2012']);

        self::assertEquals(1, count($events), 'got more than one event');
        self::assertEquals(4, $events[0]->getStructure()->getUid(), 'wrong kalender id for event');
    }

    public function testGetCategoryForGlobalId(): void
    {
        $cat23 = new Category();
        $cat23->setUid(23);
        $cat23->setText('Baustein 1a (alt)');

        $cat42 = new Category();
        $cat42->setUid(42);
        $cat42->setText('pl');

        $categories = $this->sn->get_categories_by_ids([23, 42]);

        self::assertCount(2, $categories, 'got more or less than two event');
        self::assertEquals($cat23, $categories[0], 'wrong Category 1');
        self::assertEquals($cat42, $categories[1], 'wrong Category 2');
    }

    public function testGetAllCategories(): void
    {
        $categories = $this->sn->get_all_categories();

        self::assertCount(987, $categories, 'got wrong number of categories');
        // TODO: check categories
    }

    public function testGetAllCategoriesForKalenderAndEvent(): void
    {
        $categories = $this->sn->get_all_categories_for_kalender_and_event(4, 123);

        self::assertCount(22, $categories, 'got wrong number of categories');
        // TODO check the categories
    }

    public function testGetKalenderElements(): void
    {
        $kalender = $this->sn->get_kalender_by_global_id('4');
        self::assertCount(1, $kalender, 'got more than one kalender');
        self::assertEquals(4, $kalender[0]->getUid(), 'wrong kalender id returned');
    }

    public function testGetIndexElements()
    {
        $indexes = $this->sn->get_index_for_global_id_with_filter(4, ['deeps' => '2']);

        self::assertGreaterThan(13, count($indexes), 'to few structures returned, as of writing there were 113');

        $diozese = $indexes['4']; // diozese Köln
        self::assertGreaterThan(5, count($diozese->getChildren()), 'less than 5 Bezirke, as of writing there were 12');

        $bezirk = $indexes['17']; // bezirk erft
        self::assertGreaterThan(5, count($bezirk->getChildren()), 'less than 5 Stämme, as of writing there were 12');
    }

    public function testException()
    {
        $ext = new ScoutNetExceptionMissingConfVar('test', 23);

        self::assertEquals(23, $ext->getCode());
        self::assertEquals("Missing 'test'. Please Contact your Admin to enter a valid credentials for ScoutNet Connect. You can request them via <a href=\"mailto:scoutnetconnect@scoutnet.de\">scoutnetConnect@ScoutNet.de</a>.", $ext->getMessage());
    }

    public function testSetScoutnetConnectDataFailureAES_KEY(): void
    {
        $this->expectException(ScoutNetExceptionMissingConfVar::class);
        $this->expectExceptionMessage('Missing \'aes_key\'.');

        $this->sn->set_scoutnet_connect_data('https://www.scoutnet.de/community/scoutnetConnect.html', self::API_PROVIDER, '', self::AES_IV);
    }

    public function testSetScoutnetConnectDataFailureAES_IV(): void
    {
        $this->expectException(ScoutNetExceptionMissingConfVar::class);
        $this->expectExceptionMessage('Missing \'aes_iv\'.');

        $this->sn->set_scoutnet_connect_data('https://www.scoutnet.de/community/scoutnetConnect.html', self::API_PROVIDER, self::AES_KEY, '');
    }

    public function testSetScoutnetConnectDataFailureProvider(): void
    {
        $this->expectException(ScoutNetExceptionMissingConfVar::class);
        $this->expectExceptionMessage('Missing \'provider\'.');

        $this->sn->set_scoutnet_connect_data('https://www.scoutnet.de/community/scoutnetConnect.html', '', self::AES_KEY, self::AES_IV);
    }

    public function testSetScoutnetConnectDataFailureLoginurl(): void
    {
        $this->expectException(ScoutNetExceptionMissingConfVar::class);
        $this->expectExceptionMessage('Missing \'login_url\'.');

        $this->sn->set_scoutnet_connect_data('  ', self::API_PROVIDER, self::AES_KEY, self::AES_IV);
    }

    public function testSetScoutnetConnectDataDefaultValues(): void
    {
        $this->sn->set_scoutnet_connect_data();

        $objectReflection = new ReflectionObject($this->sn);
        $login_url = $objectReflection->getProperty('login_url');
        $login_url->setAccessible(true);

        self::assertEquals('https://www.scoutnet.de/community/scoutnetConnect.html', $login_url->getValue($this->sn));
    }

    public function testScoutNetConnectLogin(): void
    {
        $this->_setCorrectWriteCredentials();

        $connect_button = $this->sn->get_scoutnet_connect_login_button('http://localhost/testclient.php', true, 'https://www.scoutnet.de/images/scoutnetConnect.png', 'de');
        $expected_connect_button = file_get_contents(CACHE_DIR . 'ConnectButton.html');

        self::assertEquals($expected_connect_button, $connect_button);
    }

    public function testSetLoginDetailsUserEmpty(): void
    {
        $this->expectException(ScoutNetExceptionMissingConfVar::class);
        $this->expectExceptionCode(1492695814);

        $this->sn->loginUser('', self::API_KEY);
        $objectReflection = new ReflectionObject($this->sn);
        $method = $objectReflection->getMethod('_check_login');
        $method->setAccessible(true);

        $method->invokeArgs($this->sn, []);
    }

    public function testSetLoginDetailsApiKeyEmpty()
    {
        $this->expectException(ScoutNetExceptionMissingConfVar::class);
        $this->expectExceptionCode(1491938183);

        $this->sn->loginUser(self::API_USER, '');
        $objectReflection = new ReflectionObject($this->sn);
        $method = $objectReflection->getMethod('_check_login');
        $method->setAccessible(true);

        $method->invokeArgs($this->sn, []);
    }

    public function testSetLoginDetailsApiKeyWrongLength()
    {
        $this->expectException(ScoutNetExceptionMissingConfVar::class);
        $this->expectExceptionCode(1491938183);

        $this->sn->loginUser(self::API_USER, 'abc');
        $objectReflection = new ReflectionObject($this->sn);
        $method = $objectReflection->getMethod('_check_login');
        $method->setAccessible(true);

        $method->invokeArgs($this->sn, []);
    }

    public function testSetLoginDetails()
    {
        $this->sn->loginUser(self::API_USER, self::API_KEY);
        $objectReflection = new ReflectionObject($this->sn);
        $method = $objectReflection->getMethod('_check_login');
        $method->setAccessible(true);

        $ret = $method->invokeArgs($this->sn, []);
        self::assertEquals('', $ret);
    }

    public function testGenerateAuthWithoutApiKey()
    {
        $this->expectException(ScoutNetExceptionMissingConfVar::class);
        $this->expectExceptionCode(1491938183);

        $this->sn->loginUser(self::API_USER, 'wrongApiKey');
        // call to private function
        $objectReflection = new ReflectionObject($this->sn);
        $method = $objectReflection->getMethod('_generate_auth');
        $method->setAccessible(true);

        $method->invokeArgs($this->sn, ['test']);
    }

    public function testGenerateAuthWithCorrectApiKey(): void
    {
        $this->sn->loginUser(self::API_USER, self::API_KEY);

        // call to private function
        $objectReflection = new ReflectionObject($this->sn);
        $method = $objectReflection->getMethod('_generate_auth');
        $method->setAccessible(true);

        $auth = $method->invokeArgs($this->sn, ['test']);

        $exp = 'cGJcjkxp40dKZP6Cf8QfpCiqDcXTRmrD50zdjtjBATWDeSMbj0Ro0etFtMJBASd-NBn41PC-y6IvI-h2QejUNm7g9IVpaSJj1_ibUSDoSwVNTdS_c0RSem8XyO-gTrl78gVH0AnJ13B9PUDj_mMsuquZ3teK7pJvZ5ynur4mNxc~'; // pkcs#7
        // $exp = "cGJcjkxp40dKZP6Cf8QfpCiqDcXTRmrD50zdjtjBATWDeSMbj0Ro0etFtMJBASd-NBn41PC-y6IvI-h2QejUNm7g9IVpaSJj1_ibUSDoSwVNTdS_c0RSem8XyO-gTrl78gVH0AnJ13B9PUDj_mMsuhmTe_YWh-DuQ-x4ZTlM3IQ~"; // nullPadding
        self::assertEquals($exp, $auth);
    }

    public function testGetApiKeyFromDataWithNoAuth(): void
    {
        $this->_setCorrectWriteCredentials();

        $data = $this->sn->getApiKeyFromData();

        self::assertFalse($data);
    }

    public function testGetApiKeyFromDataWithEmptyAuth(): void
    {
        $this->expectException(ScoutNetException::class);
        $this->expectExceptionMessage('AUTH is empty');

        $this->_setCorrectWriteCredentials();

        // we mocked the time for the API
        $_GET['auth'] = 'AAAA';
        $this->sn->getApiKeyFromData();
    }

    public function testGetApiKeyFromDataWithBrokenMd5()
    {
        $this->expectException(ScoutNetException::class);
        $this->expectExceptionMessage('Could not verify AUTH');

        $this->_setCorrectWriteCredentials();

        // we mocked the time for the API
        $_GET['auth'] = $this->_generateAuth(self::MOCKED_TIME_VALUE, self::API_PROVIDER, self::API_USER, self::API_KEY, true, false);
        $this->sn->getApiKeyFromData();
    }

    public function testGetApiKeyFromDataWithBrokenSha1()
    {
        $this->expectException(ScoutNetException::class);
        $this->expectExceptionMessage('Could not verify AUTH');

        $this->_setCorrectWriteCredentials();

        // we mocked the time for the API
        $_GET['auth'] = $this->_generateAuth(self::MOCKED_TIME_VALUE, self::API_PROVIDER, self::API_USER, self::API_KEY, false, true);
        $this->sn->getApiKeyFromData();
    }

    public function testGetApiKeyFromDataWithExpiredTime()
    {
        $this->expectException(ScoutnetException::class);
        $this->expectExceptionMessage('AUTH is too old');

        $this->_setCorrectWriteCredentials();

        // we mocked the time for the API
        $_GET['auth'] = $this->_generateAuth(\ScoutNet\Api\time() - 5000);
        $this->sn->getApiKeyFromData();
    }

    public function testGetApiKeyFromDataWithWrongProvider()
    {
        $this->expectException(ScoutnetException::class);
        $this->expectExceptionMessage('AUTH for wrong provider');

        $this->_setCorrectWriteCredentials();

        // we mocked the time for the API
        $_GET['auth'] = $this->_generateAuth(self::MOCKED_TIME_VALUE, 'wrongProvider');
        $this->sn->getApiKeyFromData();
    }

    public function testGetApiKeyFromData()
    {
        $this->_setCorrectWriteCredentials();

        // we mocked the time for the API
        $_GET['auth'] = $this->_generateAuth();

        // this function gets the data from $_GET['auth']
        $data = $this->sn->getApiKeyFromData();

        $scoutnetUser = $data['user'];
        $api_key = $data['api_key'];

        self::assertEquals(self::API_USER, $scoutnetUser);
        self::assertEquals(self::API_KEY, $api_key);
    }

    public function testGetApiKeyFromDataCheckCache()
    {
        $this->_setCorrectWriteCredentials();

        // we mocked the time for the API
        $_GET['auth'] = $this->_generateAuth();

        $first_run = $this->sn->getApiKeyFromData();
        $second_run = $this->sn->getApiKeyFromData();

        self::assertEquals($first_run, $second_run);
    }

    public function testWritePermissions(): void
    {
        $this->sn->loginUser(self::API_USER, self::API_KEY);
        $rights = $this->sn->has_write_permission_to_calender(1);

        self::assertEquals(Permission::AUTH_WRITE_ALLOWED, $rights);
    }

    public function testWritePermissionsNoAuth(): void
    {
        $this->sn->loginUser(self::API_USER, self::API_KEY);
        $rights = $this->sn->has_write_permission_to_calender(2);

        self::assertEquals(Permission::AUTH_NO_RIGHT, $rights);
    }

    public function testWritePermissionsPending(): void
    {
        $this->sn->loginUser(self::API_USER, self::API_KEY);
        $rights = $this->sn->has_write_permission_to_calender(3);

        self::assertEquals(Permission::AUTH_REQUEST_PENDING, $rights);
    }

    public function testRequestPermissionWorking()
    {
        $this->sn->loginUser(self::API_USER, self::API_KEY);
        // ask for rights
        $ret = $this->sn->request_write_permissions_for_calender(1);

        self::assertEquals(Permission::AUTH_REQUESTED, $ret['code']);
    }

    public function testRequestPermissionAlreadyRequested()
    {
        $this->sn->loginUser(self::API_USER, self::API_KEY);
        // ask for rights
        $ret = $this->sn->request_write_permissions_for_calender(2);

        self::assertEquals(Permission::AUTH_REQUEST_PENDING, $ret['code']);
    }

    public function testRequestSingleEvent()
    {
        $cat = new Category();
        $cat->setUid(1);
        $cat->setText('Sonstiges');

        $cat858 = new Category();
        $cat858->setUid(858);
        $cat858->setText('AGMedien');
        $cat4 = new Category();
        $cat4->setUid(4);
        $cat4->setText('Aktion');
        $cat882 = new Category();
        $cat882->setUid(882);
        $cat882->setText('Bausteinwochenende');
        $cat7 = new Category();
        $cat7->setUid(7);
        $cat7->setText('Bezirksleitung');
        $cat12 = new Category();
        $cat12->setUid(12);
        $cat12->setText('Diözesanleitung');
        $cat3 = new Category();
        $cat3->setUid(3);
        $cat3->setText('Fahrt/Lager');
        $cat105 = new Category();
        $cat105->setUid(105);
        $cat105->setText('Freunde und Förderer');
        $cat5 = new Category();
        $cat5->setUid(5);
        $cat5->setText('Gemeinde');
        $cat11 = new Category();
        $cat11->setUid(11);
        $cat11->setText('Gremium/AK');
        $cat182 = new Category();
        $cat182->setUid(182);
        $cat182->setText('Gruppenstunde');
        $cat810 = new Category();
        $cat810->setUid(810);
        $cat810->setText('Jamb');
        $cat43 = new Category();
        $cat43->setUid(43);
        $cat43->setText('Leiterrunde');
        $cat125 = new Category();
        $cat125->setUid(125);
        $cat125->setText('Ranger & Rover');
        $cat59 = new Category();
        $cat59->setUid(59);
        $cat59->setText('Ranger/Rover');
        $cat10 = new Category();
        $cat10->setUid(10);
        $cat10->setText('Schulung/Kurs');
        $cat2 = new Category();
        $cat2->setUid(2);
        $cat2->setText('Stamm');
        $cat9 = new Category();
        $cat9->setUid(9);
        $cat9->setText('Stufenkonferenz');
        $cat881 = new Category();
        $cat881->setUid(881);
        $cat881->setText('Teamer Starter Training');
        $cat38 = new Category();
        $cat38->setUid(38);
        $cat38->setText('Truppstunde');
        $cat6 = new Category();
        $cat6->setUid(6);
        $cat6->setText('Vorstände');

        $cat886 = new Category();
        $cat886->setUid(886);
        $cat886->setText('Vorgruppe');
        $cat16 = new Category();
        $cat16->setUid(16);
        $cat16->setText('Wölflinge');
        $cat17 = new Category();
        $cat17->setUid(17);
        $cat17->setText('Jungpfadfinder');
        $cat18 = new Category();
        $cat18->setUid(18);
        $cat18->setText('Pfadfinder');
        $cat19 = new Category();
        $cat19->setUid(19);
        $cat19->setText('Rover');
        $cat20 = new Category();
        $cat20->setUid(20);
        $cat20->setText('Leiter');
        $cat476 = new Category();
        $cat476->setUid(476);
        $cat476->setText('Einstieg Schritt 1');
        $cat179 = new Category();
        $cat179->setUid(179);
        $cat179->setText('Einstieg Schritt 2');
        $cat331 = new Category();
        $cat331->setUid(331);
        $cat331->setText('Baustein 1 a');
        $cat330 = new Category();
        $cat330->setUid(330);
        $cat330->setText('Baustein 1 b');
        $cat477 = new Category();
        $cat477->setUid(477);
        $cat477->setText('Baustein 1 c');
        $cat865 = new Category();
        $cat865->setUid(865);
        $cat865->setText('Baustein 1 d');
        $cat478 = new Category();
        $cat478->setUid(478);
        $cat478->setText('Baustein 2 a');
        $cat479 = new Category();
        $cat479->setUid(479);
        $cat479->setText('Baustein 2 b');
        $cat333 = new Category();
        $cat333->setUid(333);
        $cat333->setText('Baustein 2 c');
        $cat480 = new Category();
        $cat480->setUid(480);
        $cat480->setText('Baustein 2 d');
        $cat820 = new Category();
        $cat820->setUid(820);
        $cat820->setText('Baustein 2 e');
        $cat332 = new Category();
        $cat332->setUid(332);
        $cat332->setText('Baustein 3 a');
        $cat328 = new Category();
        $cat328->setUid(328);
        $cat328->setText('Baustein 3 b');
        $cat481 = new Category();
        $cat481->setUid(481);
        $cat481->setText('Baustein 3 c');
        $cat483 = new Category();
        $cat483->setUid(483);
        $cat483->setText('Baustein 3 e');
        $cat484 = new Category();
        $cat484->setUid(484);
        $cat484->setText('Baustein 3 f');
        $cat485 = new Category();
        $cat485->setUid(485);
        $cat485->setText('Woodbadgekurs');
        $cat36 = new Category();
        $cat36->setUid(36);
        $cat36->setText('Ausbildungstagung');
        $cat486 = new Category();
        $cat486->setUid(486);
        $cat486->setText('Modulleitungstraining (MLT)');
        $cat701 = new Category();
        $cat701->setUid(701);
        $cat701->setText('Teamer-Training I');
        $cat702 = new Category();
        $cat702->setUid(702);
        $cat702->setText('Teamer-Training II');
        $cat489 = new Category();
        $cat489->setUid(489);
        $cat489->setText('Assistent Leader Training (ALT)');
        $cat897 = new Category();
        $cat897->setUid(897);
        $cat897->setText('Fort-/Weiterbildung');

        $structure = new Structure();
        $structure->setUid('4');
        $structure->setLevel('Diözese');
        $structure->setName('Köln');
        $structure->setVerband('DPSG');
        $structure->setIdent('Diözesanverband Köln');
        $structure->setLevelId(7);
        $structure->setUsedCategories([
            858 => $cat858,
            4 => $cat4,
            882 => $cat882,
            7 => $cat7,
            12 => $cat12,
            3 => $cat3,
            105 => $cat105,
            5 => $cat5,
            11 => $cat11,
            182 => $cat182,
            810 => $cat810,
            43 => $cat43,
            125 => $cat125,
            59 => $cat59,
            10 => $cat10,
            2 => $cat2,
            9 => $cat9,
            881 => $cat881,
            38 => $cat38,
            6 => $cat6,
        ]);
        $structure->setForcedCategories([
            'sections/leaders' => [886 => $cat886, 16 => $cat16, 17 => $cat17, 18 => $cat18, 19 => $cat19, 20 => $cat20],
            'DPSG-Ausbildung' => [476 => $cat476, 179 => $cat179, 331 => $cat331, 330 => $cat330, 477 => $cat477, 865 => $cat865, 478 => $cat478, 479 => $cat479, 333 => $cat333, 480 => $cat480, 820 => $cat820, 332 => $cat332, 328 => $cat328, 481 => $cat481, 483 => $cat483, 484 => $cat484, 485 => $cat485, 36 => $cat36, 486 => $cat486, 701 => $cat701, 702 => $cat702, 489 => $cat489, 897 => $cat897],
        ]);

        $kalenderUser = new User();
        $kalenderUser->setUsername('kalender-1.0');
        $kalenderUser->setFirstName('Kalender');
        $kalenderUser->setLastName('1.0');
        $kalenderUser->setSex('m');
        $kalenderUser->setUid('kalender-1.0');

        $event = new Event();
        $event->setUid(792);
        $event->setTitle('Bezirksvorständestreffen');
        $event->setOrganizer('');
        $event->setTargetGroup('');
        $event->setStartDate(DateTime::createFromFormat('Y-m-d H:i:s', '2001-03-15 00:00:00'));
        $event->setStartTime('19:30');
        $event->setEndDate(DateTime::createFromFormat('Y-m-d H:i:s', '2001-03-15 00:00:00'));
        $event->setEndTime('19:30');
        $event->setZip('');
        $event->setLocation('');
        $event->setUrlText('');
        $event->setUrl('');
        $event->setDescription("im Diözesanzentrum Rolandstraße\n\n(Autor: Webteam (mfl))");
        $event->setSections([]);
        $event->setCategories([1 => $cat]);
        $event->setStructure($structure);
        $event->setChangedBy($kalenderUser);
        $event->setCreatedBy($kalenderUser);
        $event->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2003-07-15 00:40:29'));
        $event->setChangedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2003-07-15 00:40:29'));

        $ret = $this->sn->get_events_with_ids(4, [792]);
        self::assertEquals([$event], $ret);
    }

    public function testCreateEvent(): void
    {
        // so we have the structure in cache
        self::assertNotNull($this->sn->get_kalender_by_global_id(4));

        $cat = new Category();
        $cat->setUid(1);
        $cat->setText('Sonstiges');

        $structure = $this->getTestStructure();

        $kalenderUser = new User();
        $kalenderUser->setUsername('kalender-1.0');
        $kalenderUser->setUid('kalender-1.0');

        $event = new Event('Bezirksvorständetreffen', new DateTime('2001-03-15'));
        $event->setUid(-1);
        $event->setTitle('Bezirksvorständestreffen');
        $event->setOrganizer('');
        $event->setTargetGroup('');
        // $event->setStartDate(DateTime::createFromFormat('Y-m-d H:i:s', '2001-03-15 00:00:00'));
        $event->setStartTime('19:30');
        $event->setEndDate(DateTime::createFromFormat('Y-m-d H:i:s', '2001-03-15 00:00:00'));
        $event->setEndTime('19:30');
        $event->setZip('');
        $event->setLocation('');
        $event->setUrlText('');
        $event->setUrl('');
        $event->setDescription("im Diözesanzentrum Rolandstraße\n\n(Autor: Webteam (mfl))");
        $event->setSections([]);
        $event->setCategories([1 => $cat]);
        $event->setStructure($structure);
        $event->setChangedBy($kalenderUser);
        $event->setCreatedBy($kalenderUser);
        $event->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2003-07-15 00:40:29'));
        $event->setChangedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2003-07-15 00:40:29'));

        $this->sn->loginUser(self::API_USER, self::API_KEY);
        $res = $this->sn->write_event($event);

        // the answer does not contain changedBy and createdBy, since they are not cached
        $event->setUid(23);
        $event->setChangedBy(null);
        $event->setCreatedBy(null);
        $event->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-06-02 21:55:11'));
        $event->setChangedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-06-02 21:55:11'));

        self::assertEquals($event, $res);
    }

    public function testUpdateEvent(): void
    {
        // so we have the structure in cache
        self::assertNotNull($this->sn->get_kalender_by_global_id(4));

        $cat = new Category();
        $cat->setUid(1);
        $cat->setText('Sonstiges');

        $structure = $this->getTestStructure();

        $kalenderUser = new User();
        $kalenderUser->setUsername('kalender-1.0');
        $kalenderUser->setUid('kalender-1.0');

        $event = new Event();
        $event->setUid(792);
        $event->setTitle('Bezirksvorständestreffen');
        $event->setOrganizer('');
        $event->setTargetGroup('');
        $event->setStartDate(DateTime::createFromFormat('Y-m-d H:i:s', '2001-03-15 00:00:00'));
        $event->setStartTime('19:30');
        $event->setEndDate(DateTime::createFromFormat('Y-m-d H:i:s', '2001-03-15 00:00:00'));
        $event->setEndTime('19:30');
        $event->setZip('');
        $event->setLocation('');
        $event->setUrlText('');
        $event->setUrl('');
        $event->setDescription("im Diözesanzentrum Rolandstraße\n\n(Autor: Webteam (mfl))");
        $event->setSections([]);
        $event->setCategories([1 => $cat]);
        $event->setStructure($structure);
        $event->setChangedBy($kalenderUser);
        $event->setCreatedBy($kalenderUser);
        $event->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2003-07-15 00:40:29'));
        $event->setChangedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2003-07-15 00:40:29'));

        $this->sn->loginUser(self::API_USER, self::API_KEY);
        $res = $this->sn->write_event($event);

        // the answer does not contain changedBy and createdBy, since they are not cached
        $event->setChangedBy(null);
        $event->setCreatedBy(null);
        $event->setChangedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-06-02 21:55:11'));

        self::assertEquals($event, $res);
    }

    public function testDeleteEvent(): void
    {
        $this->sn->loginUser(self::API_USER, self::API_KEY);
        $res = $this->sn->delete_event(4, 23);

        self::assertEquals(['type' => 'ok', 'content' => ['Code' => 0, 'Value' => 'object Deleted']], $res);
    }

    private function getTestStructure(): Structure
    {
        $cat = new Category();
        $cat->setUid(1);
        $cat->setText('Sonstiges');

        $cat858 = new Category();
        $cat858->setUid(858);
        $cat858->setText('AGMedien');
        $cat4 = new Category();
        $cat4->setUid(4);
        $cat4->setText('Aktion');
        $cat882 = new Category();
        $cat882->setUid(882);
        $cat882->setText('Bausteinwochenende');
        $cat7 = new Category();
        $cat7->setUid(7);
        $cat7->setText('Bezirksleitung');
        $cat12 = new Category();
        $cat12->setUid(12);
        $cat12->setText('Diözesanleitung');
        $cat3 = new Category();
        $cat3->setUid(3);
        $cat3->setText('Fahrt/Lager');
        $cat105 = new Category();
        $cat105->setUid(105);
        $cat105->setText('Freunde und Förderer');
        $cat5 = new Category();
        $cat5->setUid(5);
        $cat5->setText('Gemeinde');
        $cat11 = new Category();
        $cat11->setUid(11);
        $cat11->setText('Gremium/AK');
        $cat182 = new Category();
        $cat182->setUid(182);
        $cat182->setText('Gruppenstunde');
        $cat810 = new Category();
        $cat810->setUid(810);
        $cat810->setText('Jamb');
        $cat43 = new Category();
        $cat43->setUid(43);
        $cat43->setText('Leiterrunde');
        $cat125 = new Category();
        $cat125->setUid(125);
        $cat125->setText('Ranger & Rover');
        $cat59 = new Category();
        $cat59->setUid(59);
        $cat59->setText('Ranger/Rover');
        $cat10 = new Category();
        $cat10->setUid(10);
        $cat10->setText('Schulung/Kurs');
        $cat2 = new Category();
        $cat2->setUid(2);
        $cat2->setText('Stamm');
        $cat9 = new Category();
        $cat9->setUid(9);
        $cat9->setText('Stufenkonferenz');
        $cat881 = new Category();
        $cat881->setUid(881);
        $cat881->setText('Teamer Starter Training');
        $cat38 = new Category();
        $cat38->setUid(38);
        $cat38->setText('Truppstunde');
        $cat6 = new Category();
        $cat6->setUid(6);
        $cat6->setText('Vorstände');

        $cat886 = new Category();
        $cat886->setUid(886);
        $cat886->setText('Vorgruppe');
        $cat16 = new Category();
        $cat16->setUid(16);
        $cat16->setText('Wölflinge');
        $cat17 = new Category();
        $cat17->setUid(17);
        $cat17->setText('Jungpfadfinder');
        $cat18 = new Category();
        $cat18->setUid(18);
        $cat18->setText('Pfadfinder');
        $cat19 = new Category();
        $cat19->setUid(19);
        $cat19->setText('Rover');
        $cat20 = new Category();
        $cat20->setUid(20);
        $cat20->setText('Leiter');
        $cat476 = new Category();
        $cat476->setUid(476);
        $cat476->setText('Einstieg Schritt 1');
        $cat179 = new Category();
        $cat179->setUid(179);
        $cat179->setText('Einstieg Schritt 2');
        $cat331 = new Category();
        $cat331->setUid(331);
        $cat331->setText('Baustein 1 a');
        $cat330 = new Category();
        $cat330->setUid(330);
        $cat330->setText('Baustein 1 b');
        $cat477 = new Category();
        $cat477->setUid(477);
        $cat477->setText('Baustein 1 c');
        $cat865 = new Category();
        $cat865->setUid(865);
        $cat865->setText('Baustein 1 d');
        $cat478 = new Category();
        $cat478->setUid(478);
        $cat478->setText('Baustein 2 a');
        $cat479 = new Category();
        $cat479->setUid(479);
        $cat479->setText('Baustein 2 b');
        $cat333 = new Category();
        $cat333->setUid(333);
        $cat333->setText('Baustein 2 c');
        $cat480 = new Category();
        $cat480->setUid(480);
        $cat480->setText('Baustein 2 d');
        $cat820 = new Category();
        $cat820->setUid(820);
        $cat820->setText('Baustein 2 e');
        $cat332 = new Category();
        $cat332->setUid(332);
        $cat332->setText('Baustein 3 a');
        $cat328 = new Category();
        $cat328->setUid(328);
        $cat328->setText('Baustein 3 b');
        $cat481 = new Category();
        $cat481->setUid(481);
        $cat481->setText('Baustein 3 c');
        $cat483 = new Category();
        $cat483->setUid(483);
        $cat483->setText('Baustein 3 e');
        $cat484 = new Category();
        $cat484->setUid(484);
        $cat484->setText('Baustein 3 f');
        $cat485 = new Category();
        $cat485->setUid(485);
        $cat485->setText('Woodbadgekurs');
        $cat36 = new Category();
        $cat36->setUid(36);
        $cat36->setText('Ausbildungstagung');
        $cat486 = new Category();
        $cat486->setUid(486);
        $cat486->setText('Modulleitungstraining (MLT)');
        $cat701 = new Category();
        $cat701->setUid(701);
        $cat701->setText('Teamer-Training I');
        $cat702 = new Category();
        $cat702->setUid(702);
        $cat702->setText('Teamer-Training II');
        $cat489 = new Category();
        $cat489->setUid(489);
        $cat489->setText('Assistent Leader Training (ALT)');
        $cat897 = new Category();
        $cat897->setUid(897);
        $cat897->setText('Fort-/Weiterbildung');
        $cat950 = new Category();
        $cat950->setUid(950);
        $cat950->setText('Leiter*innen');

        $cat952 = new Category();
        $cat952->setUid(952);
        $cat952->setText('Biber');

        $structure = new Structure();
        $structure->setUid('4');
        $structure->setLevel('Diözese');
        $structure->setName('Köln');
        $structure->setVerband('DPSG');
        $structure->setIdent('Diözesanverband Köln');
        $structure->setLevelId(7);
        $structure->setUsedCategories([
            4 => $cat4,
            7 => $cat7,
            12 => $cat12,
            3 => $cat3,
            5 => $cat5,
            11 => $cat11,
            182 => $cat182,
            43 => $cat43,
            59 => $cat59,
            10 => $cat10,
            2 => $cat2,
            9 => $cat9,
            38 => $cat38,
            6 => $cat6,
            950 => $cat950,
        ]);
        $structure->setForcedCategories([
            'sections/leaders' => [16 => $cat16, 17 => $cat17, 18 => $cat18, 19 => $cat19, 20 => $cat20, 952 => $cat952],
            'DPSG-Ausbildung' => [476 => $cat476, 179 => $cat179, 331 => $cat331, 330 => $cat330, 477 => $cat477, 865 => $cat865, 478 => $cat478, 479 => $cat479, 333 => $cat333, 480 => $cat480, 820 => $cat820, 332 => $cat332, 328 => $cat328, 481 => $cat481, 483 => $cat483, 484 => $cat484, 485 => $cat485, 36 => $cat36, 486 => $cat486, 701 => $cat701, 702 => $cat702, 489 => $cat489, 897 => $cat897],
        ]);

        return $structure;
    }
}

/**
 * Mocks
 */
define('CACHE_DIR', dirname(__DIR__) . '/Fixtures/');

class JsonRPCClientHelperMock extends JsonRPCClientHelper
{
    private string $cache_dir;

    public function __construct(string $url, bool $debug = false, string $cache_dir = null)
    {
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
     * @throws Exception
     */
    public function __call($method, $params)
    {
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

        $cache_file = $this->cache_dir . $method . '.json';
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

    public function setData($type, $id, $data, $api_user, $auth)
    {
        // we only return the data
        $data['Last_Modified_At'] = 1717365311;
        $data['Last_Modified_By'] = $api_user;

        if (!is_numeric($id)) {
            $id = -1;
        }

        if ($id == -1) {
            $data['Created_At'] = 1717365311;
            $data['Created_By'] = $api_user;
            $data['UID'] = 23;
            $data['ID'] = 23;
        }

        return $data;
    }

    public function deleteObject($type, $ssid, $id, $api_user, $auth)
    {
        return ['type' => 'ok', 'content' => ['Code' => 0, 'Value' => 'object Deleted']];
    }
}

/**
 * We mock time() and rand() so we have predictable values.
 */

namespace ScoutNet\Api;

use ScoutNet\Api\Tests\Unit\ApiTest;

/**
 * mock time
 *
 * @return int
 */
function time()
{
    return ApiTest::MOCKED_TIME_VALUE;
}

/**
 * mock random
 *
 * @return int
 */
function rand()
{
    return ApiTest::MOCKED_RAND_VALUE;
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
