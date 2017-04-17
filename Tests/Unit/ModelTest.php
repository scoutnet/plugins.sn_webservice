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

        $this->assertEquals(null, $categorie->getUid());
        $this->assertEquals('', $categorie->getText());
        $this->assertEquals(false, $categorie->getAvailable());
    }

    public function testSetParameter() {
        $categorie = new Categorie();

        $categorie->setUid(23);
        $this->assertEquals(23, $categorie->getUid());

        $categorie->setText('demo');
        $this->assertEquals('demo', $categorie->getText());

        $categorie->setAvailable(true);
        $this->assertEquals(true, $categorie->getAvailable());
    }
}

class TestEventModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf( Event::class, new Event());
    }

    public function testDefaultParameters() {
        $event = new Event();

        $this->assertEquals(null, $event->getUid());
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

    public function testSetParameter() {
        $event = new Event();

        $event->setUid(23);
        $this->assertEquals(23, $event->getUid());

        $event->setTitle('demoTitle');
        $this->assertEquals('demoTitle', $event->getTitle());

        $event->setOrganizer('demoOrganizer');
        $this->assertEquals('demoOrganizer', $event->getOrganizer());

        $event->setTargetGroup('demoTargetGroup');
        $this->assertEquals('demoTargetGroup', $event->getTargetGroup());

        $start = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-09 00:00:00');
        $event->setStartDate($start);
        $this->assertEquals($start, $event->getStartDate());

        $event->setStartTime("10:00");
        $this->assertEquals("10:00", $event->getStartTime());

        $end = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-10 00:00:00');
        $event->setEndDate($end);
        $this->assertEquals($end, $event->getEndDate());

        $event->setEndTime("23:00");
        $this->assertEquals("23:00", $event->getEndTime());

        $event->setZip('demoZip');
        $this->assertEquals('demoZip', $event->getZip());

        $event->setLocation('demoLocation');
        $this->assertEquals('demoLocation', $event->getLocation());

        $event->setUrlText('demoUrlText');
        $this->assertEquals('demoUrlText', $event->getUrlText());

        $event->setUrl('demoUrl');
        $this->assertEquals('demoUrl', $event->getUrl());

        $event->setDescription('demoDescription');
        $this->assertEquals('demoDescription', $event->getDescription());
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

    public function testSetParameters() {
        $permission = new Permission();

        $permission->setState(Permission::AUTH_REQUEST_PENDING);
        $this->assertEquals(Permission::AUTH_REQUEST_PENDING, $permission->getState());

        $permission->setText('demoText');
        $this->assertEquals('demoText', $permission->getText());

        $permission->setType('demoType');
        $this->assertEquals('demoType', $permission->getType());

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

class TestStructureModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf( Structure::class, new Structure());
    }

    public function testDefaultParameters() {
        $structure = new Structure();

        $this->assertEquals(null, $structure->getUid());
        $this->assertEquals(null, $structure->getEbene());
        $this->assertEquals(null, $structure->getName());
        $this->assertEquals(null, $structure->getVerband());
        $this->assertEquals(null, $structure->getIdent());
        $this->assertEquals(null, $structure->getEbeneId());
        $this->assertEquals([], $structure->getUsedCategories());
        $this->assertEquals([], $structure->getForcedCategories());
    }

    public function testSetParameter() {
        $structure = new Structure();

        $structure->setUid(23);
        $this->assertEquals(23, $structure->getUid());

        $structure->setEbene('demoEbene');
        $this->assertEquals('demoEbene', $structure->getEbene());

        $structure->setName("demoName");
        $this->assertEquals("demoName", $structure->getName());

        $structure->setVerband('demoVerband');
        $this->assertEquals('demoVerband', $structure->getVerband());

        $structure->setIdent("demoIdent");
        $this->assertEquals("demoIdent", $structure->getIdent());

        $structure->setEbeneId(23);
        $this->assertEquals(23, $structure->getEbeneId());

        $structure->setUsedCategories(['categorie_1', 'categorie_2']);
        $this->assertEquals(['categorie_1', 'categorie_2'], $structure->getUsedCategories());

        $structure->setForcedCategories(['categorie_3', 'categorie_4']);
        $this->assertEquals(['categorie_3', 'categorie_4'], $structure->getForcedCategories());

        $this->assertEquals("demoEbene demoName", $structure->getLongName());

        $structure->setEbeneId(1);
        $this->assertEquals("demoEbene", $structure->getLongName());
    }
}

class TestStufeModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf( Stufe::class, new Stufe());
    }

    public function testDefaultParameters() {
        $stufe = new Stufe();

        $this->assertEquals(null, $stufe->getUid());
        $this->assertEquals(null, $stufe->getVerband());
        $this->assertEquals(null, $stufe->getBezeichnung());
        $this->assertEquals(null, $stufe->getFarbe());
        $this->assertEquals(-1, $stufe->getStartalter());
        $this->assertEquals(-1, $stufe->getEndalter());
        $this->assertEquals(-1, $stufe->getCategorieId());
    }

    public function testSetParameter() {
        $stufe = new Stufe();

        $stufe->setUid(23);
        $this->assertEquals(23, $stufe->getUid());

        $stufe->setVerband('demoVerband');
        $this->assertEquals('demoVerband', $stufe->getVerband());

        $stufe->setBezeichnung("demoName");
        $this->assertEquals("demoName", $stufe->getBezeichnung());

        $stufe->setFarbe('demoFarbe');
        $this->assertEquals('demoFarbe', $stufe->getFarbe());

        $stufe->setStartalter(9);
        $this->assertEquals(9, $stufe->getStartalter());

        $stufe->setEndalter(23);
        $this->assertEquals(23, $stufe->getEndalter());

        $stufe->setCategorieId(42);
        $this->assertEquals(42, $stufe->getCategorieId());

        $this->assertEquals("<img src='https://kalender.scoutnet.de/2.0/images/23.gif' alt='demoName' />", $stufe->getImageURL());
    }
}

class TestUserModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf( User::class, new User());
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
        $this->assertEquals(23, $user->getUid());

        $user->setUsername('demoUsername');
        $this->assertEquals('demoUsername', $user->getUsername());

        $user->setFirstName("demoFirstName");
        $this->assertEquals("demoFirstName", $user->getFirstName());

        $user->setLastName('demoLastName');
        $this->assertEquals('demoLastName', $user->getLastName());

        $user->setSex(User::SEX_FEMALE);
        $this->assertEquals(User::SEX_FEMALE, $user->getSex());

        $this->assertEquals("demoFirstName demoLastName", $user->getFullName());
        $this->assertEquals("demoFirstName demoLastName (demoUsername)", $user->getLongName());

        $user->setFirstName(null);
        $user->setLastName(null);

        // if the firstname is empty we get the Username
        $this->assertEquals("demoUsername", $user->getFirstName());
        $this->assertEquals("demoUsername", $user->getFullName());

        $this->assertEquals("demoUsername", $user->getLongName());
    }
}
