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

namespace ScoutNet\Api\Helpers;

use InvalidArgumentException;

/**
 * AESHelper
 */
class AesHelper
{
    private string $z;
    private string $iv;
    private string $cypher;

    /** constructs an AES cipher using a specific key.
     *
     * @param        $z
     * @param string $mode
     * @param string $iv
     */
    public function __construct($z, string $mode = 'CBC', string $iv = '1234567890123456')
    {
        $Nk = strlen($z) / 4;

        if ($Nk !== 4 && $Nk !== 6 && $Nk !== 8) {
            throw new InvalidArgumentException('Key is ' . ($Nk * 32) . ' bits long. *not* 128, 192, or 256.', 1572194460);
        }

        $this->cypher = 'aes-' . ($Nk * 32) . '-' . strtolower($mode);

        $this->z = $z;

        $xsize = strlen($iv);
        $this->iv = '';
        for ($j = 0; $j < 16; $j++) {
            if (($j) < $xsize) {
                $this->iv[$j] = $iv[$j];
            } else
                $this->iv[$j] = chr(0);
        }
    }

    /** Encrypts an arbitrary length String.
     *
     * @params plaintext string
     * @param $x
     *
     * @return string
     */
    public function encrypt($x): string
    {
        // This is on purpose, since it comes from the constructor
        /** @noinspection EncryptionInitializationVectorRandomnessInspection */
        return openssl_encrypt($x, $this->cypher, $this->z, OPENSSL_RAW_DATA, $this->iv);
    }

    /** Decrypts an arbitrary length String.
     *
     * @params ciphertext string
     * @param $y
     *
     * @return string
     */
    public function decrypt($y): string
    {
        return openssl_decrypt($y, $this->cypher, $this->z, OPENSSL_RAW_DATA, $this->iv);
    }
}
