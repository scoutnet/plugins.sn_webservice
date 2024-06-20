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

namespace ScoutNet\Api\Tests\Unit\Helpers;

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

    public function testEncryptDecrypt(): void
    {
        $key = [
            'key' => '12345678901234567890123456789012',
            'iv' => '1234567890123456',
            'mode' => AesHelper::AES_MODE_CBC,
        ];

        $pt = 'testtest';

        $aes = new AesHelper($key['key'], $key['mode'], $key['iv']);

        $crypt = $aes->encrypt($pt);

        $aes = new AesHelper($key['key'], $key['mode'], $key['iv']);

        self::assertEquals($pt, $aes->decrypt($crypt));
    }

    public static function dataProviderCorrectKeyLength(): array
    {
        return [
            'aes128' => [
                '1234567890123456',
            ],
            'aes192' => [
                '123456789012345678901234',
            ],
            'aes256' => [
                '12345678901234567890123456789012',
            ],
            'empty' => [
                '',
                [1572194460],
            ],
            'wrong length' => [
                '123',
                [1572194460],
            ],
        ];
    }

    /**
     * @param string $key
     * @param array $expExceptions
     *
     * @dataProvider dataProviderCorrectKeyLength
     */
    public function testCorrectKeyLength(string $key, array $expExceptions = []): void
    {
        if ($expExceptions && count($expExceptions) > 0) {
            foreach ($expExceptions as $expExc) {
                $this->expectExceptionCode($expExc);
            }
        }
        new AesHelper($key);
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

    public static function dataProviderEncrypt(): array
    {
        return [
            'short iv' => [ // should be padded with 0x00
                [
                    'key' => '12345678901234567890123456789012',
                    'iv' => '1234567890123', // will be padded by 0x00
                    'mode' => AesHelper::AES_MODE_CBC,
                ],
                'testtest',
                'CZSjsb+lRidyF3vAYKbbzA==', // pkcs#7
                // 'zckkIjpRYuWW19m0QtvxTw==', // zero padded
            ],
            'short block' => [ // should be padded with 0x00
                [
                    'key' => '12345678901234567890123456789012',
                    'iv' => '1234567890123456',
                    'mode' => AesHelper::AES_MODE_CBC,
                ],
                'testtest',
                'FCbM1hpe5vAbYvq3LQv5yg==', // pkcs#7
                // 'ruIH7F3mHozAP9aU5cZD1A==', // zero padded
            ],
            'no padding' => [
                [
                    'key' => '12345678901234567890123456789012',
                    'iv' => '1234567890123456',
                    'mode' => AesHelper::AES_MODE_CBC,
                ],
                'testtesttesttest',
                'O4p8xIJFm5/EHinKjrB/U5WyJAFMhVe4NbeHe514eOw=', // pkcs#7
                // 'O4p8xIJFm5/EHinKjrB/Uw==', // no padding
            ],
            'more than one block' => [
                [
                    'key' => '12345678901234567890123456789012',
                    'iv' => '1234567890123456',
                    'mode' => AesHelper::AES_MODE_CBC,
                ],
                'testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest',
                'O4p8xIJFm5/EHinKjrB/U8ySfWjP9s0J3fTbi3/0LcXmNVWbRvcvaCin63IuFcOE9Cg6nQylpSabUfW9m/WO+8r87PO7U2P/JcqN8lSzoErLoVmPjYF3YM0AgAGTCk6ns5JWKJ/gvJC3FhmA8Tmw8OpbXuKosGDU2kZRrzHKMkk=', // pkcs#7
                // 'O4p8xIJFm5/EHinKjrB/U8ySfWjP9s0J3fTbi3/0LcXmNVWbRvcvaCin63IuFcOE9Cg6nQylpSabUfW9m/WO+8r87PO7U2P/JcqN8lSzoErLoVmPjYF3YM0AgAGTCk6ns5JWKJ/gvJC3FhmA8Tmw8LYp1olEN+pE8rhBu5yG328=', // zero padding
            ],
            'testEncryptionECBPKCS7Padding' => [
                [
                    'key' => '12345678901234567890123456789012',
                    'iv' => '1234567890123456',
                    'mode' => AesHelper::AES_MODE_ECB,
                ],
                'dies ist ein sehr langer test mit sehr viel text..',
                'FinfuxuBJA6LrPr226ldeZwPFpGLvQqih/3CTH/6k1vqbO4VTuptsCVCKe1gwnZnMYEABJau5z7IvUiaJ7cjtw==',
            ],
            'testEncryptionCBCPKCS7Padding' => [
                [
                    'key' => '12345678901234567890123456789012',
                    'iv' => '1234567890123456',
                    'mode' => AesHelper::AES_MODE_CBC,
                ],
                'dies ist ein sehr langer test mit sehr viel text..',
                'aKRxFwuhKcfJ4kNprkJQPoMkOUDYrEOKKGe4olQqFc0YjLF2d5P1/FV+qz/K4I1RiqvgKpCGGqvKn3uP9FC90Q==',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderEncrypt
     *
     * @param array $key
     * @param string $plainText
     * @param string $cypherText
     */
    public function testEncrypt(array $key, string $plainText, string $cypherText): void
    {
        $aes = new AESHelper($key['key'], $key['mode'], $key['iv']);
        $crypt = base64_encode($aes->encrypt($plainText));

        self::assertEquals($cypherText, $crypt);
    }

    public static function dataProviderDecrypt(): array
    {
        return [
            'less than one block' => [
                [
                    'key' => '12345678901234567890123456789012',
                    'iv' => '1234567890123456',
                    'mode' => AesHelper::AES_MODE_CBC,
                ],
                'FCbM1hpe5vAbYvq3LQv5yg==', // pkcs#7
                // 'ruIH7F3mHozAP9aU5cZD1A==', // zero padded
                'testtest',
            ],
            'not aligned - less than one block' => [
                [
                    'key' => '12345678901234567890123456789012',
                    'iv' => '1234567890123456',
                    'mode' => AesHelper::AES_MODE_CBC,
                ],
                'ruIH7F3mHozAP9aU5cZD', // missing chars
                '', // error in decoding
                // base64_decode('bih5rTSfnoaUeGjstwajBA=='),
            ],
            'exact one block' => [
                [
                    'key' => '12345678901234567890123456789012',
                    'iv' => '1234567890123456',
                    'mode' => AesHelper::AES_MODE_CBC,
                ],
                'O4p8xIJFm5/EHinKjrB/U5WyJAFMhVe4NbeHe514eOw=', // pkcs#7
                // 'O4p8xIJFm5/EHinKjrB/Uw==', // no padding
                'testtesttesttest',
            ],
            'more than one block' => [
                [
                    'key' => '12345678901234567890123456789012',
                    'iv' => '1234567890123456',
                    'mode' => AesHelper::AES_MODE_CBC,
                ],
                'O4p8xIJFm5/EHinKjrB/U8ySfWjP9s0J3fTbi3/0LcXmNVWbRvcvaCin63IuFcOE9Cg6nQylpSabUfW9m/WO+8r87PO7U2P/JcqN8lSzoErLoVmPjYF3YM0AgAGTCk6ns5JWKJ/gvJC3FhmA8Tmw8OpbXuKosGDU2kZRrzHKMkk=', // pkcs#7 padding
                // 'O4p8xIJFm5/EHinKjrB/U8ySfWjP9s0J3fTbi3/0LcXmNVWbRvcvaCin63IuFcOE9Cg6nQylpSabUfW9m/WO+8r87PO7U2P/JcqN8lSzoErLoVmPjYF3YM0AgAGTCk6ns5JWKJ/gvJC3FhmA8Tmw8LYp1olEN+pE8rhBu5yG328=', // zero padding
                'testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest',
            ],
            //            'broken length' => [
            //                [
            //                    'key' => '12345678901234567890123456789012',
            //                    'iv' => '1234567890123456',
            //                    'mode' => AesHelper::AES_MODE_CBC,
            //                ],
            //                'O4p8xIJFm5/EHinKjrB/U8ySfWjP9s0J3fTbi3/0LcXmNVWbRvcvaCin63IuFcOE9Cg6nQylpSabUfW9m/WO+8r87PO7U2P/JcqN8lSzoErLoVmPjYF3YM0AgAGTCk6ns5JWKJ/gvJC3FhmA8Tmw8OpbXuKosGDU2kZRrzHKMk',
            //                false,
            //            ],
            'testDecryptionECBPKCS7Padding' => [
                [
                    'key' => '12345678901234567890123456789012',
                    'iv' => '1234567890123456',
                    'mode' => AesHelper::AES_MODE_ECB,
                ],
                'FinfuxuBJA6LrPr226ldeZwPFpGLvQqih/3CTH/6k1vqbO4VTuptsCVCKe1gwnZnMYEABJau5z7IvUiaJ7cjtw==',
                'dies ist ein sehr langer test mit sehr viel text..',
            ],
            'testDecryptionCBCPKCS7Padding' => [
                [
                    'key' => '12345678901234567890123456789012',
                    'iv' => '1234567890123456',
                    'mode' => AesHelper::AES_MODE_CBC,
                ],
                'aKRxFwuhKcfJ4kNprkJQPoMkOUDYrEOKKGe4olQqFc0YjLF2d5P1/FV+qz/K4I1RiqvgKpCGGqvKn3uP9FC90Q==',
                'dies ist ein sehr langer test mit sehr viel text..',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderDecrypt
     *
     * @param array $key
     * @param string $cypherText
     * @param string $plainText
     */
    public function testDecrypt(array $key, string $cypherText, string $plainText): void
    {
        $aes = new AESHelper($key['key'], $key['mode'], $key['iv']);
        $plain = $aes->decrypt(base64_decode($cypherText));

        self::assertEquals($plainText, $plain);
    }
}
