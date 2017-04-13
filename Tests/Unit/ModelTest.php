<?php
namespace ScoutNet\Api\Tests;

use PHPUnit\Framework\TestCase;
use ScoutNet\Api\Models\Categorie;
use ScoutNet\Api\Models\Event;
use ScoutNet\Api\Models\Index;
use ScoutNet\Api\Models\Permission;
use ScoutNet\Api\Models\Structure;
use ScoutNet\Api\Models\Stufe;
use ScoutNet\Api\Models\User;

class TestCategorieModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf( Categorie::class, new Categorie());
    }

    public function testDefaultParameters() {
        $categorie = new Categorie();

        $this->assertEquals(-1, $categorie->getUid());
        $this->assertEquals('', $categorie->getText());
        $this->assertEquals(false, $categorie->getAvailable());
    }
}

class TestEventModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf( Event::class, new Event());
    }

    public function testDefaultParameters() {
        $event = new Event();

        $this->assertEquals(-1, $event->getUid());
        $this->assertEquals('', $event->getTitle());
        $this->assertEquals('', $event->getOrganizer());
        $this->assertEquals('', $event->getTargetGroup());
        $this->assertEquals(null, $event->getStartDate());
        $this->assertEquals(null, $event->getStartTime());
        $this->assertEquals(null, $event->getEndDate());
        $this->assertEquals(null, $event->getEndTime());
        $this->assertEquals(null, $event->getZip());
        $this->assertEquals(null, $event->getLocation());
        $this->assertEquals(null, $event->getUrlText());
        $this->assertEquals(null, $event->getUrl());
        $this->assertEquals(null, $event->getDescription());
        $this->assertEquals([], $event->getStufen());
        $this->assertEquals([], $event->getCategories());
        $this->assertEquals(null, $event->getStructure());
        $this->assertEquals(null, $event->getChangedBy());
        $this->assertEquals(null, $event->getCreatedBy());
        $this->assertEquals(null, $event->getCreatedAt());
        $this->assertEquals(null, $event->getChangedAt());
    }
}

class TestIndexModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf( Index::class, new Index());
    }

    public function testDefaultParameters() {
        $index = new Index();

        // TODO: make to object
        /*
        $this->assertEquals(-1, $index->getUid());
        $this->assertEquals('', $index->getTitle());
        $this->assertEquals('', $index->getOrganizer());
        $this->assertEquals('', $index->getTargetGroup());
        $this->assertEquals(null, $index->getStartDate());
        $this->assertEquals(null, $index->getStartTime());
        $this->assertEquals(null, $index->getEndDate());
        $this->assertEquals(null, $index->getEndTime());
        $this->assertEquals(null, $index->getZip());
        $this->assertEquals(null, $index->getLocation());
        $this->assertEquals(null, $index->getUrlText());
        $this->assertEquals(null, $index->getUrl());
        $this->assertEquals(null, $index->getDescription());
        $this->assertEquals([], $index->getStufen());
        $this->assertEquals([], $index->getCategories());
        $this->assertEquals(null, $index->getStructure());
        $this->assertEquals(null, $index->getChangedBy());
        $this->assertEquals(null, $index->getCreatedBy());
        $this->assertEquals(null, $index->getCreatedAt());
        $this->assertEquals(null, $index->getChangedAt());
        */
    }
}

class TestPermissionModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf( Permission::class, new Permission());
    }

    public function testDefaultParameters() {
        $permission = new Permission();

        $this->assertEquals(Permission::AUTH_NO_RIGHT, $permission->getState());
        $this->assertEquals('', $permission->getText());
        $this->assertEquals('', $permission->getType());
    }
}

class TestStructureModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf( Structure::class, new Structure());
    }

    public function testDefaultParameters() {
        $structure = new Structure();

        $this->assertEquals(-1, $structure->getUid());
        $this->assertEquals(null, $structure->getEbene());
        $this->assertEquals(null, $structure->getName());
        $this->assertEquals(null, $structure->getVerband());
        $this->assertEquals(null, $structure->getIdent());
        $this->assertEquals(null, $structure->getEbeneId());
        $this->assertEquals([], $structure->getUsedCategories());
        $this->assertEquals([], $structure->getForcedCategories());
    }

}

class TestStufeModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf( Stufe::class, new Stufe());
    }

    public function testDefaultParameters() {
        $stufe = new Stufe();

        $this->assertEquals(-1, $stufe->getUid());
        $this->assertEquals(null, $stufe->getVerband());
        $this->assertEquals(null, $stufe->getBezeichnung());
        $this->assertEquals(null, $stufe->getFarbe());
        $this->assertEquals(-1, $stufe->getStartalter());
        $this->assertEquals(-1, $stufe->getEndalter());
        $this->assertEquals(-1, $stufe->getCategorieId());
    }
}

class TestUserModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf( User::class, new User());
    }

    public function testDefaultParameters() {
        $user = new User();

        $this->assertEquals(-1, $user->getUid());
        $this->assertEquals(null, $user->getUsername());
        $this->assertEquals(null, $user->getFirstName());
        $this->assertEquals(null, $user->getLastName());
        $this->assertEquals(null, $user->getSex());
    }
}
