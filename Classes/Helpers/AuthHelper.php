<?php
/**
 * Copyright (c) 2009-2024 Stefan (Mütze) Horst
 *
 * I don't have the time to read through all the licences to find out
 * what they exactly say. But it's simple. It's free for non-commercial
 * projects, but as soon as you make money with it, I want my share :-)
 * (License: Free for non-commercial use)
 *
 * Authors: Stefan (Mütze) Horst <muetze@scoutnet.de>
 */

namespace ScoutNet\Api\Helpers;

use DateTime;
use Exception;
use ScoutNet\Api\Helpers\AesHelper;
use ScoutNet\Api\Exceptions\ScoutNetException;
use ScoutNet\Api\Exceptions\ScoutNetExceptionMissingConfVar;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @author	Stefan Horst <muetze@scoutnet.de>
 */
class AuthHelper
{
    /**
     * Stores the Login Data
     * @var array
     */
    private array $snData;

    public const UNSECURE_START_IV = '1234567890123456';

    /**
     * @param string $data
     *
     * @return array|mixed
     * @throws ScoutNetExceptionMissingConfVar
     * @throws Exception
     */
    public function getApiKeyFromData($extConfig, string $data): ?array
    {
        if (isset($this->snData)) {
            return $this->snData;
        }

        self::_checkConfigValues($extConfig);

        $z = $extConfig['AES_key'];
        $iv = $extConfig['AES_iv'];

        $aes = new AesHelper($z, 'CBC', $iv);

        $base64 = base64_decode(strtr($data, '-_~', '+/='));

        if (trim($base64) === '') {
            throw new ScoutNetException('the auth is empty', 1572191918);
        }

        $data = json_decode(substr($aes->decrypt($base64), strlen($iv)), true);

        if (!is_array($data)) {
            throw new ScoutNetException('the auth is broken', 1608717350);
        }

        $md5 = $data['md5']??'';
        unset($data['md5']);
        $sha1 = $data['sha1']??'';
        unset($data['sha1']);

        if (md5(json_encode($data)) != $md5) {
            throw new ScoutNetException('the auth is broken', 1572192280);
        }

        if (sha1(json_encode($data)) != $sha1) {
            throw new ScoutNetException('the auth is broken', 1572192281);
        }

        // use this so we can mock it
        /** @var DateTime $now */
        $now = GeneralUtility::makeInstance(DateTime::class);

        if ($now->getTimestamp() - $data['time'] > 3600) {
            throw new ScoutNetException('the auth is too old. Try again', 1572192282);
        }

        $your_domain = $extConfig['ScoutnetProviderName'];

        if ($data['your_domain'] != $your_domain) {
            throw new ScoutNetException('the auth is for the wrong site!. Try again', 1572192283);
        }

        $this->snData = $data;

        return $data;
    }

    /**
     * @param array $extConfig
     *
     * @throws ScoutNetExceptionMissingConfVar
     */
    private static function _checkConfigValues(array $extConfig): void
    {
        $configVars = ['AES_key', 'AES_iv', 'ScoutnetLoginPage', 'ScoutnetProviderName'];

        foreach ($configVars as $configVar) {
            if (trim($extConfig[$configVar]) == '') {
                throw new ScoutNetExceptionMissingConfVar($configVar);
            }
        }
    }

    /**
     * This Function generates Auth for given value. The auth uses this formular:
     *
     * base64(aes_256_cbc(<random block> + json([sha1=>sha1($checkValue),md5=>md5($checkValue),time=>time()])))
     *
     * the key for the aes is the api_key, the iv is self::UNSECURE_START_IV, therefore the first block is random and will be discarded on the other end
     *
     * @param string $api_key
     * @param string $checkValue
     *
     * @return string
     * @throws Exception
     */
    public function generateAuth(string $api_key, string $checkValue): string
    {
        if ($api_key == '') {
            throw new ScoutNetException('your Api Key is empty', 1572194190);
        }

        $aes = new AesHelper($api_key, 'CBC', self::UNSECURE_START_IV);

        $now = GeneralUtility::makeInstance(DateTime::class);

        $auth = [
            'sha1' => sha1($checkValue),
            'md5' => md5($checkValue),
            'time' => $now->getTimestamp(),
        ];
        $auth = json_encode($auth);

        // this is done since we use the same iv all the time
        $random = GeneralUtility::makeInstance(Random::class);
        $first_block = $random->generateRandomBytes(16);

        return strtr(base64_encode($aes->encrypt($first_block . $auth)), '+/=', '-_~');
    }
}
