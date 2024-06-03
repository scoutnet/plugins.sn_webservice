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

namespace ScoutNet\Api\Tests\Unit\Unit\Model;

use PHPUnit\Framework\TestCase;
use ScoutNet\Api\Model\Index;

class IndexModelTest extends TestCase
{
    public function testCanBeCreated()
    {
        self::assertInstanceOf(Index::class, new Index());
    }

    public function testDefaultParameters()
    {
        $index = new Index();

        self::assertNull($index->getUid());
        self::assertNull($index->getNumber());
        self::assertNull($index->getEbene());
        self::assertNull($index->getName());
        self::assertNull($index->getOrt());
        self::assertNull($index->getPlz());
        self::assertNull($index->getUrl());
        self::assertNull($index->getLatitude());
        self::assertNull($index->getLongitude());
        self::assertNull($index->getParentId());
        self::assertEquals([], $index->getChildren());
    }

    public function testSetParameter()
    {
        $child1 = new Index();
        $child1->setUid(1);

        $child2 = new Index();
        $child2->setUid(2);

        $index = new Index();

        $index->setUid(23);
        $index->setNumber('demoNumber');
        $index->setEbene('demoEbene');
        $index->setName('demoName');
        $index->setOrt('demoOrt');
        $index->setPlz('12345');
        $index->setUrl('http://demoUrl');
        $index->setLatitude(50.00);
        $index->setLongitude(6.00);
        $index->setParentId(42);
        $index->setChildren([$child1]);

        self::assertEquals(23, $index->getUid());
        self::assertEquals('demoNumber', $index->getNumber());
        self::assertEquals('demoEbene', $index->getEbene());
        self::assertEquals('demoName', $index->getName());
        self::assertEquals('demoOrt', $index->getOrt());
        self::assertEquals('12345', $index->getPlz());
        self::assertEquals('http://demoUrl', $index->getUrl());
        self::assertEquals(50.0, $index->getLatitude());
        self::assertEquals(6.0, $index->getLongitude());
        self::assertEquals(42, $index->getParentId());
        self::assertEquals([$child1], $index->getChildren());

        $index->addChild($child2);

        self::assertEquals([$child1, $child2], $index->getChildren());
    }
}
