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
use ScoutNet\Api\Helpers\CacheHelper;
use ScoutNet\Api\Model\Event;

class CacheHelperTest extends TestCase
{
    public function testCanBeCreated()
    {
        self::assertInstanceOf(CacheHelper::class, new CacheHelper());
    }

    public function testCache()
    {
        $cache = new CacheHelper();

        $event = new Event();

        // we can only insert elements with an id
        $ret = $cache->add($event);
        self::assertFalse($ret);

        // cache miss
        self::assertNull($cache->get(Event::class, 23));

        $event->setUid(23);
        $ret = $cache->add($event);
        self::assertEquals($event, $ret);

        // cache hit
        self::assertEquals($event, $cache->get(Event::class, 23));
    }
}
