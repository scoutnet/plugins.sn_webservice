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
        $converter = new ConverterHelper();

        $expected_categorie = new Categorie();

        $expected_categorie->setUid(23);
        $expected_categorie->setText('Categorie 1');
        $expected_categorie->setAvailable(false);

        $array = ['ID' => 23, 'Text' => 'Categorie 1'];
        $is_categorie = $converter->convertApiToCategorie($array);

        $this->assertEquals($expected_categorie, $is_categorie);
    }

    public function testConvertCategorieEmptyArray() {
        $converter = new ConverterHelper();

        $expected_categorie = new Categorie();

        $expected_categorie->setUid(-1);
        $expected_categorie->setText('');
        $expected_categorie->setAvailable(false);

        $array = [];
        $is_categorie = $converter->convertApiToCategorie($array);

        $this->assertEquals($expected_categorie, $is_categorie);
    }

    public function testConvertCategorieValidArrayOverCache() {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $expected_categorie = new Categorie();

        $expected_categorie->setUid(23);
        $expected_categorie->setText('Categorie 1');
        $expected_categorie->setAvailable(false);

        $array = ['ID' => 23, 'Text' => 'Categorie 1'];
        $converter->convertApiToCategorie($array);
        $cached_categorie = $cache->get(Categorie::class, 23);

        $this->assertEquals($expected_categorie, $cached_categorie);
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

