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
use ScoutNet\Api\Model\Permission;

class PermissionModelTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        self::assertInstanceOf(Permission::class, new Permission());
    }

    public function testDefaultParameters(): void
    {
        $permission = new Permission();

        self::assertEquals(Permission::AUTH_NO_RIGHT, $permission->getState());
        self::assertEquals('', $permission->getText());
        self::assertEquals('', $permission->getType());
    }

    public function testSetParameters(): void
    {
        $permission = new Permission();

        $permission->setState(Permission::AUTH_REQUEST_PENDING);
        $permission->setText('demoText');
        $permission->setType('demoType');

        self::assertEquals(Permission::AUTH_REQUEST_PENDING, $permission->getState());
        self::assertEquals('demoText', $permission->getText());
        self::assertEquals('demoType', $permission->getType());

        // test state further
        $permission->setState(Permission::AUTH_REQUEST_PENDING);
        self::assertFalse($permission->hasWritePermissions());
        self::assertTrue($permission->hasWritePermissionsPending());

        $permission->setState(Permission::AUTH_WRITE_ALLOWED);
        self::assertTrue($permission->hasWritePermissions());
        self::assertFalse($permission->hasWritePermissionsPending());

        $permission->setState(Permission::AUTH_NO_RIGHT);
        self::assertFalse($permission->hasWritePermissions());
        self::assertFalse($permission->hasWritePermissionsPending());
    }
}
