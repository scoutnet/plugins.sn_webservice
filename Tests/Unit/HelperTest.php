<?php

namespace ScoutNet\Api\Tests;

use PHPUnit\Framework\TestCase;
use ScoutNet\Api\Helpers\CacheHelper;
use ScoutNet\Api\Helpers\ConverterHelper;
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

}
