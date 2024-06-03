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
use ScoutNet\Api\Model\Category;

class CategoryModelTest extends TestCase
{
    public function testCanBeCreated()
    {
        self::assertInstanceOf(Category::class, new Category());
    }

    public function testDefaultParameters()
    {
        $Category = new Category();

        self::assertNull($Category->getUid());
        self::assertEquals('', $Category->getText());
    }

    public function testSetParameter()
    {
        $Category = new Category();

        // first set then read to see sideefects
        $Category->setUid(23);
        $Category->setText('demo');

        self::assertEquals(23, $Category->getUid());
        self::assertEquals('demo', $Category->getText());
    }
}
