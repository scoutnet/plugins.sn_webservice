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
use ScoutNet\Api\Models\User;

class UserModelTest extends TestCase
{
    public function testCanBeCreated()
    {
        self::assertInstanceOf(User::class, new User());
    }

    public function testDefaultParameters()
    {
        $user = new User();

        self::assertNull($user->getUid());
        self::assertNull($user->getUsername());
        self::assertNull($user->getFirstName());
        self::assertNull($user->getLastName());
        self::assertNull($user->getSex());
    }

    public function testSetParameter()
    {
        $user = new User();

        $user->setUid(23);
        $user->setUsername('demoUsername');
        $user->setFirstName('demoFirstName');
        $user->setLastName('demoLastName');
        $user->setSex(User::SEX_FEMALE);

        // check if values stored correct
        self::assertEquals(23, $user->getUid());
        self::assertEquals('demoUsername', $user->getUsername());
        self::assertEquals('demoFirstName', $user->getFirstName());
        self::assertEquals('demoLastName', $user->getLastName());
        self::assertEquals(User::SEX_FEMALE, $user->getSex());

        // check derived values
        self::assertEquals('demoFirstName demoLastName', $user->getFullName());
        self::assertEquals('demoFirstName demoLastName (demoUsername)', $user->getLongName());

        // if the firstname is empty we get the Username
        $user->setFirstName(null);
        $user->setLastName(null);

        self::assertEquals('demoUsername', $user->getFirstName());
        self::assertEquals('demoUsername', $user->getFullName());

        self::assertEquals('demoUsername', $user->getLongName());
    }
}
