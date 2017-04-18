<?php

namespace ScoutNet\Api\Tests;

use PHPUnit\Framework\TestCase;
use ScoutNet\Api\Helpers\AesHelper;
use ScoutNet\Api\Helpers\CacheHelper;
use ScoutNet\Api\Helpers\ConverterHelper;
use ScoutNet\Api\Models\Categorie;
use ScoutNet\Api\Models\Event;

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

        $array = ['ID'=> 23, 'Text' => 'Categorie 1'];
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

        $array = ['ID'=> 23, 'Text' => 'Categorie 1'];
        $converter->convertApiToCategorie($array);
        $cached_categorie = $cache->get(Categorie::class, 23);

        $this->assertEquals($expected_categorie, $cached_categorie);
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

    public function testEncryptionECBNoPadding() {
        $aes = new AesHelper(self::AES_KEY);

        $ciphertext = $aes->encrypt('dies ist ein sehr langer test mit sehr viel text');

        $this->assertEquals('FinfuxuBJA6LrPr226ldeZwPFpGLvQqih/3CTH/6k1vqbO4VTuptsCVCKe1gwnZn', base64_encode($ciphertext));
    }

    public function testEncryptionCBCNoPadding() {
        $aes = new AesHelper(self::AES_KEY, AesHelper::AES_MODE_CBC);

        $ciphertext = $aes->encrypt('dies ist ein sehr langer test mit sehr viel text');

        $this->assertEquals('aKRxFwuhKcfJ4kNprkJQPoMkOUDYrEOKKGe4olQqFc0YjLF2d5P1/FV+qz/K4I1R', base64_encode($ciphertext));
    }

    public function testDencryptionECBNoPadding() {
        $aes = new AesHelper(self::AES_KEY);

        $plaintext = $aes->decrypt(base64_decode('FinfuxuBJA6LrPr226ldeZwPFpGLvQqih/3CTH/6k1vqbO4VTuptsCVCKe1gwnZn'));

        $this->assertEquals('dies ist ein sehr langer test mit sehr viel text', $plaintext);
    }

    public function testDencryptionCBCNoPadding() {
        $aes = new AesHelper(self::AES_KEY, AesHelper::AES_MODE_CBC);

        $plaintext = $aes->decrypt(base64_decode('aKRxFwuhKcfJ4kNprkJQPoMkOUDYrEOKKGe4olQqFc0YjLF2d5P1/FV+qz/K4I1R'));

        $this->assertEquals('dies ist ein sehr langer test mit sehr viel text', $plaintext);
    }
}

