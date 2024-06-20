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

namespace ScoutNet\Api\Tests\Unit\Helpers\Helpers\Helpers\Helpers\Helpers\Unit\Model;

use PHPUnit\Framework\TestCase;
use ScoutNet\Api\Model\Category;

class CategoryModelTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        self::assertInstanceOf(Category::class, new Category());
    }

    public function testDefaultParameters(): void
    {
        $category = new Category();

        self::assertNull($category->getUid());
        self::assertEquals('', $category->getText());
        self::assertFalse($category->getAvailable());
    }

    public function testSetParameter(): void
    {
        $category = new Category();

        // first set then read to see side effects
        $category->setUid(23);
        $category->setText('demo');
        $category->setAvailable(true);

        self::assertEquals(23, $category->getUid());
        self::assertEquals('demo', $category->getText());
        self::assertTrue($category->getAvailable());
    }
}
