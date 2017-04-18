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

        /*
        "Used_Kategories":{
            "858":"AGMedien",
            "4":"Aktion",
            "882":"Bausteinwochenende",
            "7":"Bezirksleitung",
            "12":"Di\u00f6zesanleitung",
            "3":"Fahrt\/Lager",
            "105":"Freunde und F\u00f6rderer",
            "5":"Gemeinde",
            "11":"Gremium\/AK",
            "182":"Gruppenstunde",
            "810":"Jamb",
            "43":"Leiterrunde",
            "125":"Ranger & Rover",
            "59":"Ranger\/Rover",
            "10":"Schulung\/Kurs",
            "2":"Stamm",
            "9":"Stufenkonferenz",
            "881":"Teamer Starter Training",
            "38":"Truppstunde",
            "6":"Vorst\u00e4nde"
        },
        "Forced_Kategories":{
            "sections\/leaders":{
                "886":"Vorgruppe",
                "16":"W\u00f6lflinge",
                "17":"Jungpfadfinder",
                "18":"Pfadfinder",
                "19":"Rover",
                "20":"Leiter"
            },
            "DPSG-Ausbildung":{
                "476":"Einstieg Schritt 1",
                "179":"Einstieg Schritt 2",
                "331":"Baustein 1 a",
                "330":"Baustein 1 b",
                "477":"Baustein 1 c",
                "865":"Baustein 1 d",
                "478":"Baustein 2 a",
                "479":"Baustein 2 b",
                "333":"Baustein 2 c",
                "480":"Baustein 2 d",
                "820":"Baustein 2 e",
                "332":"Baustein 3 a",
                "328":"Baustein 3 b",
                "481":"Baustein 3 c",
                "483":"Baustein 3 e",
                "484":"Baustein 3 f",
                "485":"Woodbadgekurs",
                "36":"Ausbildungstagung",
                "486":"Modulleitungstraining (MLT)",
                "701":"Teamer-Training I",
                "702":"Teamer-Training II",
                "489":"Assistent Leader Training (ALT)",
                "897":"Fort-\/Weiterbildung"
            }
        }

        */

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

