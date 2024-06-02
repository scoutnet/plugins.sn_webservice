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

class PermissionModelTest extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf(Permission::class, new Permission());
    }

    public function testDefaultParameters() {
        $permission = new Permission();

        $this->assertEquals(Permission::AUTH_NO_RIGHT, $permission->getState());
        $this->assertEquals('', $permission->getText());
        $this->assertEquals('', $permission->getType());
    }

    public function testSetParameters() {
        $permission = new Permission();

        $permission->setState(Permission::AUTH_REQUEST_PENDING);
        $permission->setText('demoText');
        $permission->setType('demoType');

        $this->assertEquals(Permission::AUTH_REQUEST_PENDING, $permission->getState());
        $this->assertEquals('demoText', $permission->getText());
        $this->assertEquals('demoType', $permission->getType());

        // test state further
        $permission->setState(Permission::AUTH_REQUEST_PENDING);
        $this->assertEquals(false, $permission->hasWritePermissions());
        $this->assertEquals(true, $permission->hasWritePermissionsPending());

        $permission->setState(Permission::AUTH_WRITE_ALLOWED);
        $this->assertEquals(true, $permission->hasWritePermissions());
        $this->assertEquals(false, $permission->hasWritePermissionsPending());

        $permission->setState(Permission::AUTH_NO_RIGHT);
        $this->assertEquals(false, $permission->hasWritePermissions());
        $this->assertEquals(false, $permission->hasWritePermissionsPending());
    }
}
