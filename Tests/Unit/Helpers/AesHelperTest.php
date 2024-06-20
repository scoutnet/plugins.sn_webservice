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

namespace ScoutNet\Api\Tests\Unit\Helpers\Helpers;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use ScoutNet\Api\Helpers\AesHelper;

class AesHelperTest extends TestCase
{
    public const AES_IV = '1234567890123456';
    public const AES_KEY = '12345678901234567890123456789012';

    public function testCanBeCreated(): void
    {
        self::assertInstanceOf(AesHelper::class, new AesHelper(self::AES_KEY));
    }

    public function testWrongKeySize(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Key is 80 bits long. *not* 128, 192, or 256.');
        new AesHelper('1234567890');
    }

    public function testWrongIvSize(): void
    {
        $aes = new AesHelper(self::AES_KEY, AesHelper::AES_MODE_CBC, '1234567890');

        $objectReflection = new ReflectionObject($aes);
        $iv = $objectReflection->getProperty('iv');

        // gets padded with 0x00
        self::assertEquals('1234567890' . "\0\0\0\0\0\0", $iv->getValue($aes));
    }

    public function testWrongCypherTextSize(): void
    {
        $aes = new AesHelper(self::AES_KEY);

        $plaintext_unpadded = $aes->decrypt(base64_decode('1234')); // it gets zero padded
        $plaintext_padded = $aes->decrypt(base64_decode('1234AAAAAAAAAAAAAAAAAA==')); // no padding, since cypherText is correct size

        self::assertEquals($plaintext_padded, $plaintext_unpadded);
    }

    public function testEncryptionECBPKCS7Padding(): void
    {
        $aes = new AesHelper(self::AES_KEY, AesHelper::AES_MODE_ECB);

        $ciphertext = $aes->encrypt('dies ist ein sehr langer test mit sehr viel text..');

        self::assertEquals('FinfuxuBJA6LrPr226ldeZwPFpGLvQqih/3CTH/6k1vqbO4VTuptsCVCKe1gwnZnMYEABJau5z7IvUiaJ7cjtw==', base64_encode($ciphertext));
    }

    public function testEncryptionCBCPKCS7Padding(): void
    {
        $aes = new AesHelper(self::AES_KEY, AesHelper::AES_MODE_CBC);

        $ciphertext = $aes->encrypt('dies ist ein sehr langer test mit sehr viel text..');

        self::assertEquals('aKRxFwuhKcfJ4kNprkJQPoMkOUDYrEOKKGe4olQqFc0YjLF2d5P1/FV+qz/K4I1RiqvgKpCGGqvKn3uP9FC90Q==', base64_encode($ciphertext));
    }

    public function testDecryptionECBPKCS7Padding(): void
    {
        $aes = new AesHelper(self::AES_KEY, AesHelper::AES_MODE_ECB);

        $plaintext = $aes->decrypt(base64_decode('FinfuxuBJA6LrPr226ldeZwPFpGLvQqih/3CTH/6k1vqbO4VTuptsCVCKe1gwnZnMYEABJau5z7IvUiaJ7cjtw=='));

        self::assertEquals('dies ist ein sehr langer test mit sehr viel text..', $plaintext);
    }

    public function testDecryptionCBCPKCS7Padding(): void
    {
        $aes = new AesHelper(self::AES_KEY, AesHelper::AES_MODE_CBC);

        $plaintext = $aes->decrypt(base64_decode('aKRxFwuhKcfJ4kNprkJQPoMkOUDYrEOKKGe4olQqFc0YjLF2d5P1/FV+qz/K4I1RiqvgKpCGGqvKn3uP9FC90Q=='));

        self::assertEquals('dies ist ein sehr langer test mit sehr viel text..', $plaintext);
    }
}
