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

class UserModelTest extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf(User::class, new User());
    }

    public function testDefaultParameters() {
        $user = new User();

        $this->assertEquals(null, $user->getUid());
        $this->assertEquals(null, $user->getUsername());
        $this->assertEquals(null, $user->getFirstName());
        $this->assertEquals(null, $user->getLastName());
        $this->assertEquals(null, $user->getSex());
    }

    public function testSetParameter() {
        $user = new User();

        $user->setUid(23);
        $user->setUsername('demoUsername');
        $user->setFirstName("demoFirstName");
        $user->setLastName('demoLastName');
        $user->setSex(User::SEX_FEMALE);

        // check if values stored correct
        $this->assertEquals(23, $user->getUid());
        $this->assertEquals('demoUsername', $user->getUsername());
        $this->assertEquals("demoFirstName", $user->getFirstName());
        $this->assertEquals('demoLastName', $user->getLastName());
        $this->assertEquals(User::SEX_FEMALE, $user->getSex());

        // check derived values
        $this->assertEquals("demoFirstName demoLastName", $user->getFullName());
        $this->assertEquals("demoFirstName demoLastName (demoUsername)", $user->getLongName());


        // if the firstname is empty we get the Username
        $user->setFirstName(null);
        $user->setLastName(null);

        $this->assertEquals("demoUsername", $user->getFirstName());
        $this->assertEquals("demoUsername", $user->getFullName());

        $this->assertEquals("demoUsername", $user->getLongName());
    }
}
