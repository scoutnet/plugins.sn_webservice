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

namespace ScoutNet\Api\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use ScoutNet\Api\Models\Category;
use ScoutNet\Api\Models\Event;
use ScoutNet\Api\Models\Index;
use ScoutNet\Api\Models\Permission;
use ScoutNet\Api\Models\Structure;
use ScoutNet\Api\Models\Section;
use ScoutNet\Api\Models\User;

class CategoryModelTest extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf(Category::class, new Category());
    }

    public function testDefaultParameters() {
        $Category = new Category();

        $this->assertEquals(null, $Category->getUid());
        $this->assertEquals('', $Category->getText());
    }

    public function testSetParameter() {
        $Category = new Category();

        // first set then read to see sideefects
        $Category->setUid(23);
        $Category->setText('demo');

        $this->assertEquals(23, $Category->getUid());
        $this->assertEquals('demo', $Category->getText());
    }
}
