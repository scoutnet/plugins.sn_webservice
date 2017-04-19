<?php

namespace ScoutNet\Api\Tests;
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

use PHPUnit\Framework\TestCase;
use ScoutNet\Api\Helpers\AesHelper;
use ScoutNet\Api\Helpers\CacheHelper;
use ScoutNet\Api\Helpers\ConverterHelper;
use ScoutNet\Api\Models\Categorie;
use ScoutNet\Api\Models\Event;
use ScoutNet\Api\Models\Permission;
use ScoutNet\Api\Models\Structure;
use ScoutNet\Api\Models\Stufe;
use ScoutNet\Api\Models\User;

class CacheHelperTest extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf(CacheHelper::class, new CacheHelper());
    }

    public function testCache() {
        $cache = new CacheHelper();

        $event = new Event();

        // we can only insert elements with an id
        $ret = $cache->add($event);
        $this->assertEquals(false, $ret);

        // cache miss
        $this->assertEquals(null, $cache->get(Event::class, 23));

        $event->setUid(23);
        $ret = $cache->add($event);
        $this->assertEquals($event, $ret);

        // cache hit
        $this->assertEquals($event, $cache->get(Event::class, 23));
    }
}

class ConvertHelperTest extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf(ConverterHelper::class, new ConverterHelper());
    }

    public function testConvertCategorieValidArray() {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $expected_categorie = new Categorie();

        $expected_categorie->setUid(23);
        $expected_categorie->setText('Categorie 1');

        $array = ['ID' => 23, 'Text' => 'Categorie 1'];
        $is_categorie = $converter->convertApiToCategorie($array);
        $cached_categorie = $cache->get(Categorie::class, 23);

        $this->assertEquals($expected_categorie, $is_categorie);
        $this->assertEquals($expected_categorie, $cached_categorie);
    }

    public function testConvertCategorieEmptyArray() {
        $converter = new ConverterHelper();

        $expected_categorie = new Categorie();

        $expected_categorie->setUid(-1);
        $expected_categorie->setText('');

        $array = [];
        $is_categorie = $converter->convertApiToCategorie($array);

        $this->assertEquals($expected_categorie, $is_categorie);
    }

    public function testConvertPermissionValidArray() {
        $converter = new ConverterHelper();

        $expected_permission = new Permission();

        $expected_permission->setState(Permission::AUTH_WRITE_ALLOWED);
        $expected_permission->setText('Permission 1');
        $expected_permission->setType('Type 1');

        $array = ['code' => Permission::AUTH_WRITE_ALLOWED, 'text' => 'Permission 1', 'type' => 'Type 1'];
        $is_permission = $converter->convertApiToPermission($array);

        $this->assertEquals($expected_permission, $is_permission);
    }

    public function testConvertPermissionEmptyArray() {
        $converter = new ConverterHelper();

        $expected_permission = new Permission();

        $expected_permission->setState(Permission::AUTH_NO_RIGHT);
        $expected_permission->setText('');
        $expected_permission->setType('');

        $array = [];
        $is_permission = $converter->convertApiToPermission($array);

        $this->assertEquals($expected_permission, $is_permission);
    }

    public function testConvertStructureValidArray() {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $structure = new Structure();

        $structure->setUid(23);

        $structure->setEbene('demoEbene');
        $structure->setName('demoName');
        $structure->setVerband('demoVerband');
        $structure->setIdent('demoIdent');
        $structure->setEbeneId(23);

        $cat1 = new Categorie();
        $cat1->setUid(1);
        $cat1->setText('cat1');

        $cat2 = new Categorie();
        $cat2->setUid(2);
        $cat2->setText('cat2');

        $cat3 = new Categorie();
        $cat3->setUid(3);
        $cat3->setText('cat3');

        $cat4 = new Categorie();
        $cat4->setUid(4);
        $cat4->setText('cat4');

        $structure->setUsedCategories([1 => $cat1, 2 => $cat2]);
        $structure->setForcedCategories(['section1' => [3 => $cat3], 'section2' => [4 => $cat4]]);

        $array = [
            'ID' => 23,
            'Ebene' => 'demoEbene',
            'Name' => 'demoName',
            'Verband' => 'demoVerband',
            'Ident' => 'demoIdent',
            'Ebene_Id' => 23,
            'Used_Kategories' => [
                1 => 'cat1',
                2 => 'cat2'
            ],
            'Forced_Kategories' => [
                'section1' => [3 => 'cat3'],
                'section2' => [4 => 'cat4']
            ]
        ];
        $is_structure = $converter->convertApiToStructure($array);
        $cached_structure = $cache->get(Structure::class, 23);

        $this->assertEquals($structure, $is_structure);
        $this->assertEquals($structure, $cached_structure);
    }

    public function testConvertStructureValidArrayNoCategories() {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $structure = new Structure();

        $structure->setUid(23);

        $structure->setEbene('demoEbene');
        $structure->setName('demoName');
        $structure->setVerband('demoVerband');
        $structure->setIdent('demoIdent');
        $structure->setEbeneId(23);

        $structure->setUsedCategories([]);
        $structure->setForcedCategories(['section1' => [], 'section2' => []]);

        $array = [
            'ID' => 23,
            'Ebene' => 'demoEbene',
            'Name' => 'demoName',
            'Verband' => 'demoVerband',
            'Ident' => 'demoIdent',
            'Ebene_Id' => 23,
            'Used_Kategories' => [
            ],
            'Forced_Kategories' => [
                'section1' => '',
                'section2' => ''
            ]
        ];
        $is_structure = $converter->convertApiToStructure($array);
        $cached_structure = $cache->get(Structure::class, 23);

        $this->assertEquals($structure, $is_structure);
        $this->assertEquals($structure, $cached_structure);
    }

    public function testConvertStructureEmptyArray() {
        $converter = new ConverterHelper();

        $structure = new Structure();

        $structure->setUid(-1);
        $structure->setEbene('');
        $structure->setName('');
        $structure->setVerband('');
        $structure->setIdent('');
        $structure->setEbeneId(0);
        $structure->setUsedCategories([]);
        $structure->setForcedCategories([]);

        $array = [];
        $is_structure = $converter->convertApiToStructure($array);

        $this->assertEquals($structure, $is_structure);
    }

    public function testConvertStufeValidArray() {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $expected_stufe = new Stufe();

        $expected_stufe->setUid(23);
        $expected_stufe->setVerband('demoVerband');
        $expected_stufe->setBezeichnung('Stufe 1');
        $expected_stufe->setFarbe('#ffeeff');
        $expected_stufe->setStartalter(9);
        $expected_stufe->setEndalter(11);
        $expected_stufe->setCategorieId(1);

        $array = [
            'id' => 23,
            'verband' => 'demoVerband',
            'bezeichnung' => 'Stufe 1',
            'farbe' => '#ffeeff',
            'startalter' => 9,
            'endalter' => 11,
            'Keywords_ID' => 1,
        ];
        $is_stufe = $converter->convertApiToStufe($array);
        $cached_stufe = $cache->get(Stufe::class, 23);

        $this->assertEquals($expected_stufe, $is_stufe);
        $this->assertEquals($expected_stufe, $cached_stufe);
    }

    public function testConvertStufeEmptyArray() {
        $converter = new ConverterHelper();

        $expected_stufe = new Stufe();

        $expected_stufe->setUid(-1);
        $expected_stufe->setVerband('');
        $expected_stufe->setBezeichnung('');
        $expected_stufe->setFarbe('');
        $expected_stufe->setStartalter(-1);
        $expected_stufe->setEndalter(-1);
        $expected_stufe->setCategorieId(-1);

        $array = [];
        $is_stufe = $converter->convertApiToStufe($array);

        $this->assertEquals($expected_stufe, $is_stufe);
    }

    public function testConvertUserValidArray() {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $expected_user = new User();

        $expected_user->setUid('demoUsername');
        $expected_user->setUsername('demoUsername');
        $expected_user->setFirstName('demoFirstName');
        $expected_user->setLastName('demoLastName');
        $expected_user->setSex(User::SEX_FEMALE);

        $array = [
            'userid' => 'demoUsername',
            'firstname' => 'demoFirstName',
            'surname' => 'demoLastName',
            'sex' => 'w',
        ];

        $is_user = $converter->convertApiToUser($array);
        $cached_user = $cache->get(User::class, 'demoUsername');

        $this->assertEquals($expected_user, $is_user);
        $this->assertEquals($expected_user, $cached_user);
    }

    public function testConvertUserEmptyArray() {
        $converter = new ConverterHelper();

        $expected_user = new User();

        $expected_user->setUid(-1);
        $expected_user->setUsername(null);
        $expected_user->setFirstName(null);
        $expected_user->setLastName(null);
        $expected_user->setSex(null);

        $array = [];
        $is_user = $converter->convertApiToUser($array);

        $this->assertEquals($expected_user, $is_user);
    }

    public function testConvertEventValidArray() {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $structure = new Structure();
        $structure->setUid(23);

        $changedBy = new User();
        $changedBy->setUid('user1');

        $createdBy = new User();
        $createdBy->setUid('user2');

        $createdAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-11 12:23:00');
        $changedAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-12 14:42:00');

        $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-09 00:00:00');
        $endDate = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-10 00:00:00');

        $stufe = new Stufe();
        $stufe->setUid(1);
        $stufe->setBezeichnung('Stufe');
        $stufe->setCategorieId(1);

        $cat = new Categorie();
        $cat->setUid(1);
        $cat->setText('Stufe');


        $expected_event = new Event();

        $expected_event->setUid(23);
        $expected_event->setTitle('demoTitle');
        $expected_event->setOrganizer('demoOrganizer');
        $expected_event->setTargetGroup('demoTargetGroup');

        $expected_event->setStartDate($startDate);
        $expected_event->setStartTime('10:00:00');
        $expected_event->setEndDate($endDate);
        $expected_event->setEndTime('23:00:00');
        $expected_event->setZip('12345');
        $expected_event->setLocation('demoLocation');
        $expected_event->setUrlText('demoUrlText');
        $expected_event->setUrl('http://demoUrl');
        $expected_event->setDescription('demoDescription');
        $expected_event->setChangedAt($changedAt);
        $expected_event->setCreatedAt($createdAt);
        $expected_event->setCategories([1 => $cat]);

        // without cache this will be empty
        $expected_event->setChangedBy(null);
        $expected_event->setCreatedBy(null);
        $expected_event->setStructure(null);
        $expected_event->setStufen([]);

        $array = [
            "ID" => 23,
            'UID' => 23,
            "SSID" => "23",
            'Title' => 'demoTitle',
            'Organizer' => 'demoOrganizer',
            'Target_Group' => 'demoTargetGroup',
            "Start" => "1000029600",
            "End" => "1000162800",
            "All_Day" => false,
            "ZIP" => "12345",
            "Location" => "demoLocation",
            "URL_Text" => "demoUrlText",
            "URL" => "http://demoUrl",
            "Description" => "demoDescription",
            "Stufen" => [1],
            "Keywords" => ["1" => "Stufe"],
            "Kalender" => "23",
            "Last_Modified_By" => "user1",
            "Last_Modified_At" => "1000305720",
            "Created_By" => "user2",
            "Created_At" => "1000210980"
        ];

        // without cache set
        $is_event = $converter->convertApiToEvent($array);
        $cached_event = $cache->get(Event::class, 23);

        $this->assertEquals($expected_event, $is_event);
        $this->assertEquals($expected_event, $cached_event);

        // cache the elements
        $cache->add($structure);
        $cache->add($changedBy);
        $cache->add($createdBy);
        $cache->add($stufe);
        $cache->add($cat);

        $expected_event->setChangedBy($changedBy);
        $expected_event->setCreatedBy($createdBy);
        $expected_event->setStructure($structure);
        $expected_event->setStufen([$stufe]);

        // with cache set
        $is_event = $converter->convertApiToEvent($array);
        $cached_event = $cache->get(Event::class, 23);

        $this->assertEquals($expected_event, $is_event);
        $this->assertEquals($expected_event, $cached_event);
    }

    public function testConvertEventEmptyArray() {
        $converter = new ConverterHelper();

        $expected_event = new Event();

        $expected_event->setUid(-1);
        $expected_event->setTitle(null);
        $expected_event->setOrganizer(null);
        $expected_event->setTargetGroup(null);

        $expected_event->setStartDate(null);
        $expected_event->setStartTime(null);
        $expected_event->setEndDate(null);
        $expected_event->setEndTime(null);
        $expected_event->setZip(null);
        $expected_event->setLocation(null);
        $expected_event->setUrlText(null);
        $expected_event->setUrl(null);
        $expected_event->setDescription(null);
        $expected_event->setChangedAt(null);
        $expected_event->setCreatedAt(null);
        $expected_event->setChangedBy(null);
        $expected_event->setCreatedBy(null);
        $expected_event->setStructure(null);

        $array = [];
        $is_event = $converter->convertApiToEvent($array);

        $this->assertEquals($expected_event, $is_event);
    }
}


class AESHelperTest extends TestCase {
    const AES_IV = '1234567890123456';
    const AES_KEY = '12345678901234567890123456789012';

    public function testCanBeCreated() {
        $this->assertInstanceOf(AesHelper::class, new AesHelper(self::AES_KEY));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /^Key is 80 bits long[.] [*]not[*] 128, 192, or 256[.]$/
     */
    public function testWrongKeySize() {
        new AesHelper('1234567890');
    }

    public function testWrongIvSize() {
        $aes = new AesHelper(self::AES_KEY, AesHelper::AES_MODE_CBC, '1234567890');

        $objectReflection = new \ReflectionObject($aes);
        $iv = $objectReflection->getProperty('iv');
        $iv->setAccessible(true);

        // gets padded with 0x00
        $this->assertEquals(['1', '2', '3', '4', '5', '6', '7', '8',
            '9', '0', "\0", "\0", "\0", "\0", "\0", "\0"], $iv->getValue($aes));
    }

    public function testWrongCyphertextSize() {
        $aes = new AesHelper(self::AES_KEY);

        $plaintext_unpadded = $aes->decrypt(base64_decode('1234')); // it gets zero padded
        $plaintext_padded = $aes->decrypt(base64_decode('1234AAAAAAAAAAAAAAAAAA==')); // no padding, since cyphertext is correct size

        $this->assertEquals($plaintext_padded, $plaintext_unpadded);
    }

    public function testEncryptionECBZeroPadding() {
        $aes = new AesHelper(self::AES_KEY);

        $ciphertext = $aes->encrypt('dies ist ein sehr langer test mit sehr viel text..');

        $this->assertEquals('FinfuxuBJA6LrPr226ldeZwPFpGLvQqih/3CTH/6k1vqbO4VTuptsCVCKe1gwnZn8LuJfNDe+7NyaIucraohtg==', base64_encode($ciphertext));
    }

    public function testEncryptionCBCZeroPadding() {
        $aes = new AesHelper(self::AES_KEY, AesHelper::AES_MODE_CBC);

        $ciphertext = $aes->encrypt('dies ist ein sehr langer test mit sehr viel text..');

        $this->assertEquals('aKRxFwuhKcfJ4kNprkJQPoMkOUDYrEOKKGe4olQqFc0YjLF2d5P1/FV+qz/K4I1RegSP0UpT0VskDRn0tr0W2Q==', base64_encode($ciphertext));
    }

    public function testDencryptionECBZeroPadding() {
        $aes = new AesHelper(self::AES_KEY);

        $plaintext = $aes->decrypt(base64_decode('FinfuxuBJA6LrPr226ldeZwPFpGLvQqih/3CTH/6k1vqbO4VTuptsCVCKe1gwnZn8LuJfNDe+7NyaIucraohtg=='));

        $this->assertEquals('dies ist ein sehr langer test mit sehr viel text..', $plaintext);
    }

    public function testDencryptionCBCZeroPadding() {
        $aes = new AesHelper(self::AES_KEY, AesHelper::AES_MODE_CBC);

        $plaintext = $aes->decrypt(base64_decode('aKRxFwuhKcfJ4kNprkJQPoMkOUDYrEOKKGe4olQqFc0YjLF2d5P1/FV+qz/K4I1RegSP0UpT0VskDRn0tr0W2Q=='));

        $this->assertEquals('dies ist ein sehr langer test mit sehr viel text..', $plaintext);
    }
}

