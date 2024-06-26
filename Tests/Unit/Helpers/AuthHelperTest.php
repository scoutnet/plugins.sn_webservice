<?php
/**
 * Copyright (c) 2005-2024 Stefan (Mütze) Horst
 *
 * I don't have the time to read through all the licences to find out
 * what they exactly say. But it's simple. It's free for non-commercial
 * projects, but as soon as you make money with it, I want my share :-)
 * (License: Free for non-commercial use)
 *
 * Authors: Stefan (Mütze) Horst <muetze@scoutnet.de>
 */

namespace ScoutNet\Api\Tests\Unit\Helpers;

use Exception;
use PHPUnit\Framework\TestCase;
use ScoutNet\Api\Exceptions\ScoutNetException;
use ScoutNet\Api\Exceptions\ScoutNetExceptionMissingConfVar;
use ScoutNet\Api\Helpers\AuthHelper;

class AuthHelperTest extends TestCase
{
    protected AuthHelper $authHelper;

    public function setup(): void
    {
        parent::setUp();
        $this->authHelper = new AuthHelper();
    }

    /**
     * @return array
     */
    public static function dataProviderGenerateAuth(): array
    {
        return [
            'normal' => [
                '12345678901234567890123456789012',
                'test',
                // '1TxlYBdYb-H0MwwsznIm47NsnDfKTDkh7Ah8btarmCk6FyeXvvGSXZli5mX-nl14EBHiwi0uMNVTtO7aVPbkPMP4uy-rteK4Uww5pF-gRYF4hl1X4Uw0AhFfr57HasNCDnMvLtzp2_Eo5M_0n37d6A-wP7zJ_xaIMR904tLCBQE~', // null padding
                '1TxlYBdYb-H0MwwsznIm47NsnDfKTDkh7Ah8btarmCk6FyeXvvGSXZli5mX-nl14EBHiwi0uMNVTtO7aVPbkPMP4uy-rteK4Uww5pF-gRYF4hl1X4Uw0AhFfr57HasNCDnMvLtzp2_Eo5M_0n37d6A6c0Zz6tyi5lZoSK72whg0~', // pkcs#7
                // base64(aes_256_cbc('4444444444444444' . json_encode(['md5'=>md5('test'),'sha1'=>sha1('test'),'time'=>1234])))
            ],
            'no Api Key' => [
                '',
                '',
                '',
                [1572194190],
            ],
        ];
    }

    /**
     * @param string $apiKey
     * @param string $checkValue
     * @param string $expectedResult
     * @param array $expectedExceptions
     *
     * @throws Exception
     * @dataProvider dataProviderGenerateAuth
     */
    public function testGenerateAuth(string $apiKey, string $checkValue, string $expectedResult, array $expectedExceptions = []): void
    {
        if ($expectedExceptions && count($expectedExceptions) > 0) {
            foreach ($expectedExceptions as $expExc) {
                $this->expectExceptionCode($expExc);
            }
        } else {
            $GLOBALS['fixed_time'] = 1234;
        }

        $ret = $this->authHelper->generateAuth($apiKey, $checkValue);

        self::assertEquals($expectedResult, $ret);
    }

    public static function dataProviderGetApiKeyFromData(): array
    {
        return [
            'empty auth' => [
                '',
                [1572191918],
            ],
            'no md5' => [
                // '1TxlYBdYb-H0MwwsznIm49YD0B5NSjIHiHEQ3d5GMCj9bzTJFWGEWFS2DoZJY9-eZOQBH3xb_2z5WL_R0QXr3UgAYBHPKmtGweeJ322baVGg1zpNbHGFbRNKcqQsWmDBXqfzDe7UjwJWQQFxqtQe1EPRUgMSqu642kf82-zfXWaKSaNoYMzs6vDAEr92qjry', // null padding
                '1TxlYBdYb-H0MwwsznIm49YD0B5NSjIHiHEQ3d5GMCj9bzTJFWGEWFS2DoZJY9-eZOQBH3xb_2z5WL_R0QXr3UgAYBHPKmtGweeJ322baVGg1zpNbHGFbRNKcqQsWmDBXqfzDe7UjwJWQQFxqtQe1HokwfvVUt-RAiwIV5UchS4~', // pkcs#7
                [1572192280],
            ],
            'broken md5' => [
                '1TxlYBdYb-H0MwwsznIm49YD0B5NSjIHiHEQ3d5GMCj9bzTJFWGEWFS2DoZJY9-eZOQBH3xb_2z5WL_R0QXr3eJEshkStKL2LkZcb4LOQSDIF_EkXowP_xBJj7DW4bS5OJBKEPq4Mu7ais9CusAm2QjGP5CAp1L6erdwgemKw7hK4r58bYSdTewcBPhfFIar', // pkcs#7
                [1572192280],
            ],
            'no sha1' => [
                // '1TxlYBdYb-H0MwwsznIm49YD0B5NSjIHiHEQ3d5GMCj9bzTJFWGEWFS2DoZJY9-eZOQBH3xb_2z5WL_R0QXr3UgAYBHPKmtGweeJ322baVEY1clhmSc25hhkRlXP9n8o6Y2Jt76wnT7ni0Q9pElTAasw4UmbJyhucfohNvxhfjY~', // null padding
                '1TxlYBdYb-H0MwwsznIm49YD0B5NSjIHiHEQ3d5GMCj9bzTJFWGEWFS2DoZJY9-eZOQBH3xb_2z5WL_R0QXr3WbSGxr74B9Hl1XLIw3_eUY7rgeRreY6iYcVQNBhOV-0DaiMgD4v8mLLyN1I4plSKj7VGHdbzPPQn9NXPRwnZL0~', // pkcs#7
                [1572192281],
            ],
            'broken sha1' => [
                '1TxlYBdYb-H0MwwsznIm49YD0B5NSjIHiHEQ3d5GMCj9bzTJFWGEWFS2DoZJY9-eZOQBH3xb_2z5WL_R0QXr3WbSGxr74B9Hl1XLIw3_eUY7rgeRreY6iYcVQNBhOV-0DaiMgD4v8mLLyN1I4plSKjJ6Naks3nVhkPC194WN2ixGNguirKEs1wqA6XH46Ia9', // pkcs#7
                [1572192281],
            ],
            'too old' => [
                // current time is always older than timestamp 1234
                // '1TxlYBdYb-H0MwwsznIm49YD0B5NSjIHiHEQ3d5GMCj9bzTJFWGEWFS2DoZJY9-eZOQBH3xb_2z5WL_R0QXr3UgAYBHPKmtGweeJ322baVGg1zpNbHGFbRNKcqQsWmDBXqfzDe7UjwJWQQFxqtQe1EPRUgMSqu642kf82-zfXWZBaqw85SGz2BGL-fs1nzsTfkKbHRR-mpoaRdExA0Awurh1LHCVH2ROGxKBmkzPetI~', // null padding
                '1TxlYBdYb-H0MwwsznIm49YD0B5NSjIHiHEQ3d5GMCj9bzTJFWGEWFS2DoZJY9-eZOQBH3xb_2z5WL_R0QXr3WfS5oY-mJwfn7dWJIrnRMxmBlQioQQZUr4e1i832qtqY2IsgigAEUAPTJhAXaN_24-Vpc2RBW3OkDpfAKvfP8N8nSRFL4kQbghCWzwslDl1uCb-uYyw61GOlpnoy66ItoYZKn6DyYVwJ0mbxKbDOFs~', // pkcs#7
                [1572192282],
            ],
            'wrong site' => [
                // '1TxlYBdYb-H0MwwsznIm49YD0B5NSjIHiHEQ3d5GMChAozkulOICYVK4nwow8joAAXD7cuFAU06XSNLxHVDD1Ao6Z98MxJWqBD0IxKbwkVtZgfftjTeK4i5NHVFwqUfYwEFqXJj7QVIUdWTCEwbZzNjTXIHI1woW-FgDhBxE-quit0C4yxRFj6YE-R-pUBmetgsdlrbwc3S9ZdTwz6PGI34aV6snflbd81tuovwTUBc~', // null padding
                '1TxlYBdYb-H0MwwsznIm49YD0B5NSjIHiHEQ3d5GMCj4ucqExsOIRACWHVROAZujDcAJN_7CQPTH_8aS2KhyobQ3NlzHvKWMzprAbVqL_JsmEumG--svPeocJ3Ebz6eYoMXaFhJjkYHfxklP3OYXZUAHwXbOhegPMBs3T_mVZDeCbrU9vUxqaRbc_hcomby6zsNoqhi9jstaxUW29WSyLklpsBm-PBNHW3cyHW_v_us~', // pkcs#7
                [1572192283],
                false,
                true, // fix time
            ],
            'correct_auth' => [
                // '1TxlYBdYb-H0MwwsznIm49YD0B5NSjIHiHEQ3d5GMCj9bzTJFWGEWFS2DoZJY9-eZOQBH3xb_2z5WL_R0QXr3UgAYBHPKmtGweeJ322baVGg1zpNbHGFbRNKcqQsWmDBXqfzDe7UjwJWQQFxqtQe1EPRUgMSqu642kf82-zfXWZBaqw85SGz2BGL-fs1nzsTfkKbHRR-mpoaRdExA0Awurh1LHCVH2ROGxKBmkzPetI~', // null padding
                '1TxlYBdYb-H0MwwsznIm49YD0B5NSjIHiHEQ3d5GMCj9bzTJFWGEWFS2DoZJY9-eZOQBH3xb_2z5WL_R0QXr3WbSGxr74B9Hl1XLIw3_eUY7rgeRreY6iYcVQNBhOV-0DaiMgD4v8mLLyN1I4plSKjOke3TZ10Y0j2Ju2nqjkAEDyha5rSOCTFfSTNGUdFOq_QtafreRYYYAVrhH5TjdIjIARLmx4pW9ukqxkI9wCCM~', // pkcs#7
                null,
                [
                    'your_domain' => 'unitTest',
                    'user' => 'unitTest',
                    'time' => 1234,
                ],
                true, // fix time
            ],
        ];
    }

    /**
     * @param string $data
     * @param array|null $expectedExceptions
     * @param bool $expectedReturn
     *
     * @param bool $fix_time
     *
     * @throws ScoutNetExceptionMissingConfVar
     * @throws ScoutNetException
     * @dataProvider dataProviderGetApiKeyFromData
     */
    public function testGetApiKeyFromData(string $data, ?array $expectedExceptions, bool|array $expectedReturn = false, bool $fix_time = false): void
    {
        $excConfig = [
            'AES_key' => '12345678901234567890123456789012',
            'AES_iv' => '1234567890123456',
            'ScoutnetLoginPage' => 'https://www.scoutnet.de/auth',
            'ScoutnetProviderName' => 'unitTest',
        ];

        if ($fix_time) {
            // no time
            $GLOBALS['fixed_time'] = 3600;
        }

        $exp = false;
        if ($expectedExceptions && count($expectedExceptions) > 0) {
            foreach ($expectedExceptions as $expExc) {
                $this->expectExceptionCode($expExc);
            }
            $exp = true;
        }

        $ret = $this->authHelper->getApiKeyFromData($excConfig, $data);
        if (!$exp) {
            self::assertEquals($expectedReturn, $ret);

            // check cache
            $ret = $this->authHelper->getApiKeyFromData($excConfig, $data);
            self::assertEquals($expectedReturn, $ret);
        }
    }
}

namespace ScoutNet\Api\Helpers;

function random_bytes(int $length): string
{
    return '4444444444444444';
}

function time(): int
{
    return $GLOBALS['fixed_time'] ?? \time();
}
