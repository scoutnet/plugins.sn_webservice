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
use ScoutNet\Api\Exceptions\ScoutNetException;
use ScoutNet\Api\Helpers\JsonRPCClientHelper;

class JsonRPCClientHelperTest extends TestCase
{
    public function testCanBeCreated()
    {
        self::assertInstanceOf(JsonRPCClientHelper::class, new JsonRPCClientHelper('demo'));
    }

    public function testDebugLog()
    {
        $rpcClient = new JsonRPCClientHelper('demo');

        $objectReflection = new \ReflectionObject($rpcClient);
        $debugLog = $objectReflection->getMethod('debugLog');
        $debugLog->setAccessible(true);

        // debug is off
        $debugLog->invoke($rpcClient, 'testLog');
        self::assertEquals([], $rpcClient->getDebugLog());

        // debug is on
        $rpcClient->setDebug(true);
        $debugLog->invoke($rpcClient, 'testLog');
        self::assertEquals(['testLog'], $rpcClient->getDebugLog());

        $printDebugLog = $objectReflection->getMethod('printDebugLog');
        $printDebugLog->setAccessible(true);

        // test echo
        $this->expectOutputString('testLog');
        $printDebugLog->invoke($rpcClient);
    }

    public function testRPCCallWrongMethodName(): void
    {
        $this->expectException(\TypeError::class);
        $rpcClient = new JsonRPCClientHelper('demo');
        $rpcClient->__call([], []);
    }

    public function testRPCCallWrongParameter(): void
    {
        $this->expectException(\TypeError::class);
        $rpcClient = new JsonRPCClientHelper('demo');
        $rpcClient->__call('demoCall', '');
    }

    public function testRPCCallWrongID(): void
    {
        $this->expectException(ScoutNetException::class);
        $this->expectExceptionCode(1572203283);
        $this->expectExceptionMessage('Incorrect response id (request id: 1, response id: -23)');
        $rpcClient = new JsonRPCClientHelper('demo');
        $rpcClient->__call('demoCallBrokenID', []);
    }

    public function testRPCCallError(): void
    {
        $this->expectException(ScoutNetException::class);
        $this->expectExceptionCode(1572203301);
        $this->expectExceptionMessage('demoError');
        $rpcClient = new JsonRPCClientHelper('demo');
        $rpcClient->__call('demoCallError', []);
    }

    public function testRPCNotification(): void
    {
        $rpcClient = new JsonRPCClientHelper('demo');
        $rpcClient->setRPCNotification(true);

        $ret = $rpcClient->__call('demoNotification', []);

        self::assertTrue($ret);
    }

    // Call per Fopen
    public function testRPCCallFopenCannotConnect(): void
    {
        $this->expectException(ScoutNetException::class);
        $this->expectExceptionCode(1492679926);
        $this->expectExceptionMessage('Unable to connect to demoBrokenCall');

        $rpcClient = new JsonRPCClientHelper('demoBrokenCall');
        $rpcClient->__call('demoCall', []);
    }

    public function testRPCCallFopenCookieTest(): void
    {
        $_COOKIE['XDEBUG_SESSION'] = '123';
        $rpcClient = new JsonRPCClientHelper('demo');
        $ret = $rpcClient->__call('demoCallCookie', []);
        self::assertEquals(['Cookie' => 'XDEBUG_SESSION=123'], $ret);
    }

    public function testRPCCallFopenWorking(): void
    {
        $rpcClient = new JsonRPCClientHelper('demo');

        $ret = $rpcClient->__call('demoCall', []);

        self::assertEquals(['demoAnswer'], $ret);
    }

    // Call per Curl
    public function testRPCCallCurlCookieTest(): void
    {
        $_COOKIE['XDEBUG_SESSION'] = '123';
        $rpcClient = new JsonRPCClientHelper('demo');
        $rpcClient->setUseCurl();

        $ret = $rpcClient->__call('demoCallCookie', []);
        self::assertEquals(['Cookie' => 'XDEBUG_SESSION=123'], $ret);
    }

    public function testRPCCallCurlProxyTest(): void
    {
        $rpcClient = new JsonRPCClientHelper('demo');
        $rpcClient->setUseCurl('demoProxyServer', 'demoProxyTunnel', 'demoUserPass');

        $ret = $rpcClient->__call('demoCallOptions', []);
        self::assertEquals(['options' => [
            CURLOPT_PROXY => 'demoProxyServer',
            CURLOPT_HTTPPROXYTUNNEL => 'demoProxyTunnel',
            CURLOPT_PROXYUSERPWD => 'demoUserPass',
        ]], $ret);
    }

    public function testRPCCallCurlWorking()
    {
        $rpcClient = new JsonRPCClientHelper('demo');
        $rpcClient->setUseCurl();

        $ret = $rpcClient->__call('demoCall', []);

        self::assertEquals(['demoAnswer'], $ret);
    }
}

namespace ScoutNet\Api\Helpers;

use Exception;

function fopen($url, $mode, $use_include_path = null, $context = null)
{
    switch ($url) {
        case 'demo':
            return ['url' => $url, 'context' => $context];
        case 'demoBrokenCall':
            return false;
    }
    return \fopen($url, $mode, $use_include_path, $context);
    //throw new Exception('Mock called with wrong url');
}

function getMockedRequest($url, $content, $headers = [])
{
    switch ($content['method']) {
        case 'demoCall':
            $response = [
                'id' => $content['id'],
                'error' => null,
                'result' => ['demoAnswer'],
            ];
            break;
        case 'demoCallBrokenID':
            $response = [
                'id' => -23,
                'error' => null,
                'result' => ['demoAnswer'],
            ];
            break;
        case 'demoCallError':
            $response = [
                'id' => $content['id'],
                'error' => ['message' => 'demoError', 'code' => 23],
                'result' => null,
            ];
            break;
        case 'demoCallCookie':
            $cookies = '';

            foreach ($headers as $header) {
                $header = explode(': ', $header, 2);
                if (strtolower($header[0]) == 'cookie') {
                    $cookies = $header[1];
                    // only first cookie header
                    break;
                }
            }
            $response = [
                'id' => $content['id'],
                'error' => null,
                'result' => ['Cookie' => $cookies],
            ];
            break;
        case 'demoNotification':
            $response = true;
            break;
        default:
            throw new Exception('Mock called with wrong method');
    }

    return $response;
}

function fgets(&$fd)
{
    if (is_resource($fd)) {
        // live
        return \fgets($fd);
    }

    if ($fd != null) {
        $url = $fd['url'];

        if ($url != 'demo') {
            throw new Exception('Mock called with wrong url');
        }
        $stream_context = $fd['context'];
        $opt = stream_context_get_options($stream_context);
        $post_data = $opt['http']['content'];
        $request = json_decode($post_data, true);

        $headers = explode("\r\n", $opt['http']['header']);

        $response = getMockedRequest($url, $request, $headers);

        $fd = null;
        return json_encode($response);
    }
    return false;
}

function curl_init()
{
    return [];
}

function curl_setopt_array(&$ch, $options)
{
    foreach ($options as $key => $value) {
        $ch[$key] = $value;
    }
}

function curl_setopt(&$ch, $opt, $value)
{
    $ch[$opt] = $value;
}
function curl_exec($ch)
{
    $url = $ch[CURLOPT_URL];
    $request = json_decode($ch[CURLOPT_POSTFIELDS], true);
    $headers = $ch[CURLOPT_HTTPHEADER];

    if (isset($ch[CURLOPT_COOKIE])) {
        $headers[] = 'Cookie: ' . $ch[CURLOPT_COOKIE];
    }

    if ($request['method'] === 'demoCallOptions') {
        $response = [
            'id' => $request['id'],
            'error' => null,
            'result' => [
                'options' => [],
            ],
        ];
        foreach ([CURLOPT_PROXY, CURLOPT_HTTPPROXYTUNNEL, CURLOPT_PROXYUSERPWD] as $option) {
            $response['result']['options'][$option] = $ch[$option];
        }
    } else {
        $response = getMockedRequest($url, $request, $headers);
    }

    return json_encode($response);
}

function curl_close($ch) {}
