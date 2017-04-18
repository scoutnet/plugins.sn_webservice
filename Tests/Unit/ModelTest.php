<?php
namespace ScoutNet\Api\Tests;
/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 Stefan "Mütze" Horst <muetze@scoutnet.de>, ScoutNet
 *
 *  All rights reserved
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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
        $this->assertInstanceOf(Categorie::class, new Categorie());
    }

    public function testDefaultParameters() {
        $categorie = new Categorie();

        $this->assertEquals(null, $categorie->getUid());
        $this->assertEquals('', $categorie->getText());
        $this->assertEquals(false, $categorie->getAvailable());
    }

    public function testSetParameter() {
        $categorie = new Categorie();

        // first set then read to see sideefects
        $categorie->setUid(23);
        $categorie->setText('demo');
        $categorie->setAvailable(true);

        $this->assertEquals(23, $categorie->getUid());
        $this->assertEquals('demo', $categorie->getText());
        $this->assertEquals(true, $categorie->getAvailable());
    }
}

class TestEventModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf(Event::class, new Event());
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

        // derived values
        $this->assertEquals('', $event->getStufenImages());
    }

    public function testSetParameter() {
        $event = new Event();

        $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-09 00:00:00');
        $end = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-10 00:00:00');

        // stufen helper values
        $stufe1 = new Stufe();
        $stufe1->setUid(1);
        $stufe1->setBezeichnung('Stufe1');
        $stufe1->setCategorieId(1);

        $stufe2 = new Stufe();
        $stufe2->setUid(2);
        $stufe2->setBezeichnung('Stufe2');
        $stufe2->setCategorieId(2);

        $stufe3 = new Stufe();
        $stufe3->setUid(3);
        $stufe3->setBezeichnung('Stufe3');
        $stufe3->setCategorieId(3);

        $stufe1_cat = new Categorie();
        $stufe1_cat->setUid(1);
        $stufe1_cat->setText('Stufe1');

        $stufe2_cat = new Categorie();
        $stufe2_cat->setUid(2);
        $stufe2_cat->setText('Stufe2');

        $stufe3_cat = new Categorie();
        $stufe3_cat->setUid(3);
        $stufe3_cat->setText('Stufe3');

        $cat1 = new Categorie();
        $cat1->setUid(1);
        $cat1->setText('cat_1');

        $cat2 = new Categorie();
        $cat2->setUid(2);
        $cat2->setText('cat_2');

        $cat3 = new Categorie();
        $cat3->setUid(3);
        $cat3->setText('cat_3');

        $structure = new Structure();

        $changedBy = new User();
        $changedBy->setUid(23);

        $createdBy = new User();
        $createdBy->setUid(42);

        $createdAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-11 00:00:00');
        $changedAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-12 00:00:00');

        // set values
        $event->setUid(23);
        $event->setTitle('demoTitle');
        $event->setOrganizer('demoOrganizer');
        $event->setTargetGroup('demoTargetGroup');

        $event->setStartDate($startDate);
        $event->setStartTime("10:00");
        $event->setEndDate($end);
        $event->setEndTime("23:00");

        $event->setZip('demoZip');
        $event->setLocation('demoLocation');
        $event->setUrlText('demoUrlText');
        $event->setUrl('demoUrl');
        $event->setDescription('demoDescription');


        $event->setStufen([$stufe1, $stufe2]);
        $event->setCategories([1 => $cat1, 2 => $cat2]);

        $event->setStructure($structure);

        $event->setChangedBy($changedBy);
        $event->setCreatedBy($createdBy);
        $event->setCreatedAt($createdAt);
        $event->setChangedAt($changedAt);


        // test if set correct
        $this->assertEquals(23, $event->getUid());
        $this->assertEquals('demoTitle', $event->getTitle());
        $this->assertEquals('demoOrganizer', $event->getOrganizer());
        $this->assertEquals('demoTargetGroup', $event->getTargetGroup());

        // time
        $this->assertEquals($startDate, $event->getStartDate());
        $this->assertEquals("10:00", $event->getStartTime());
        $this->assertEquals($end, $event->getEndDate());
        $this->assertEquals("23:00", $event->getEndTime());

        // derived time values
        $this->assertEquals(\DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-09 10:00:00'), $event->getStartTimestamp());
        $this->assertEquals(\DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-10 23:00:00'), $event->getEndTimestamp());

        // if starttime is null we get 00:00
        $event->setStartTime(null);
        $this->assertEquals(\DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-09 00:00:00'), $event->getStartTimestamp());

        // if endtime is null we get 00:00
        $event->setEndTime(null);
        $this->assertEquals(\DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-10 00:00:00'), $event->getEndTimestamp());

        // if enddate is null but endtime is set we get startdate endtime
        $event->setEndTime('23:00');
        $event->setEndDate(null);
        $this->assertEquals(\DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-09 23:00:00'), $event->getEndTimestamp());

        // if enddate and endtime is null we get startdate
        $event->setEndDate(null);
        $event->setEndTime(null);
        $this->assertEquals(\DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-09 00:00:00'), $event->getEndTimestamp());

        // show the EndDate only if it is set
        $this->assertEquals(false, $event->getShowEndDate());

        // show the EndDate if enddate is set
        $event->setEndDate(\DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-10 00:00:00'));
        $this->assertEquals(true, $event->getShowEndDate());

        // do not show the EndDate if start == end
        $event->setEndDate($event->getStartDate());
        $this->assertEquals(false, $event->getShowEndDate());

        // show the EndTime if it not null
        $this->assertEquals(false, $event->getShowEndTime());
        $event->setEndTime('23:00');
        $this->assertEquals(true, $event->getShowEndTime());

        // ShowEndTime is set -> endDate or Time should be the same
        $this->assertEquals(true, $event->getShowEndDateOrTime());

        // check if allDay Event (no starttime is set)
        $this->assertEquals(true, $event->getAllDayEvent());

        $event->setStartTime('10:00');
        $this->assertEquals(false, $event->getAllDayEvent());

        // check if start Month/Year are correct
        $this->assertEquals('9', $event->getStartMonth());
        $this->assertEquals('2001', $event->getStartYear());

        // check location values
        $this->assertEquals('demoZip', $event->getZip());
        $this->assertEquals('demoLocation', $event->getLocation());
        $this->assertEquals('demoUrlText', $event->getUrlText());
        $this->assertEquals('demoUrl', $event->getUrl());
        $this->assertEquals('demoDescription', $event->getDescription());

        // check if we show details
        $this->assertEquals(true, $event->getShowDetails());

        $properties = ['description', 'zip', 'location', 'organizer', 'targetGroup', 'url'];

        // do not show, if all are set to null
        foreach ($properties as $reset_property) {
            $setter = 'set'.ucfirst($reset_property);
            $event->$setter(null);
        }
        $this->assertEquals(false, $event->getShowDetails());

        // but if one is set, show it
        foreach ($properties as $property) {
            foreach ($properties as $reset_property) {
                $setter = 'set'.ucfirst($reset_property);
                $event->$setter(null);
            }
            $setter = 'set'.ucfirst($property);
            $event->$setter('demo'.ucfirst($property));

            $this->assertEquals(true, $event->getShowDetails());
        }

        // check stufe
        $this->assertEquals([$stufe1, $stufe2], $event->getStufen());
        $this->assertEquals('<img src=\'https://kalender.scoutnet.de/2.0/images/1.gif\' alt=\'Stufe1\' /><img src=\'https://kalender.scoutnet.de/2.0/images/2.gif\' alt=\'Stufe2\' />', $event->getStufenImages());

        // add stufe
        $event->addStufe($stufe3);
        $this->assertEquals([$stufe1, $stufe2, $stufe3], $event->getStufen());
        $this->assertEquals([1 => $stufe1_cat, 2 => $stufe2_cat, 3 => $stufe3_cat], $event->getStufenCategories());


        // check categorie
        $this->assertEquals([1 => $cat1, 2 => $cat2], $event->getCategories());

        // add categorie
        $event->addCategorie($cat3);
        $this->assertEquals([1 => $cat1, 2 => $cat2, 3 => $cat3], $event->getCategories());


        $this->assertEquals($structure, $event->getStructure());

        $this->assertEquals($changedBy, $event->getChangedBy());
        $this->assertEquals($createdBy, $event->getCreatedBy());
        $this->assertEquals($createdAt, $event->getCreatedAt());
        $this->assertEquals($changedAt, $event->getChangedAt());

        // derived value
        $this->assertEquals($changedBy, $event->getAuthor());

        // if changedBy is null return created By
        $event->setChangedBy(null);
        $this->assertEquals($createdBy, $event->getAuthor());


        // check if these values are correctly copied
        $copiedProperties = ['title', 'organizer', 'targetGroup', 'startDate', 'startTime', 'endDate', 'endTime',
            'zip', 'location', 'urlText', 'url', 'description', 'structure', 'stufen', 'categories'];

        $event_copy = new Event();
        $event_copy->copyProperties($event);

        foreach ($copiedProperties as $property) {
            $getter = 'get'.ucfirst($property);
            $this->assertEquals($event->$getter(), $event_copy->$getter());
        }

        // check that some others are not copied
        $this->assertEquals(null, $event_copy->getUid());
        $this->assertEquals(null, $event_copy->getChangedBy());
        $this->assertEquals(null, $event_copy->getCreatedBy());
        $this->assertEquals(null, $event_copy->getCreatedAt());
        $this->assertEquals(null, $event_copy->getChangedAt());
    }
}

class TestIndexModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf(Index::class, new Index());
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
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}

class TestPermissionModel extends TestCase {
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

class TestStructureModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf(Structure::class, new Structure());
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
        $structure->setEbene('demoEbene');
        $structure->setName("demoName");
        $structure->setVerband('demoVerband');
        $structure->setIdent("demoIdent");
        $structure->setEbeneId(23);
        $structure->setUsedCategories(['categorie_1', 'categorie_2']);
        $structure->setForcedCategories(['categorie_3', 'categorie_4']);


        $this->assertEquals(23, $structure->getUid());
        $this->assertEquals('demoEbene', $structure->getEbene());
        $this->assertEquals("demoName", $structure->getName());
        $this->assertEquals('demoVerband', $structure->getVerband());
        $this->assertEquals("demoIdent", $structure->getIdent());
        $this->assertEquals(23, $structure->getEbeneId());
        $this->assertEquals(['categorie_1', 'categorie_2'], $structure->getUsedCategories());
        $this->assertEquals(['categorie_3', 'categorie_4'], $structure->getForcedCategories());
        $this->assertEquals("demoEbene demoName", $structure->getLongName());

        // if ebene < 4 do not show name
        $structure->setEbeneId(1);
        $this->assertEquals("demoEbene", $structure->getLongName());
    }
}

class TestStufeModel extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf(Stufe::class, new Stufe());
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
        $stufe->setVerband('demoVerband');
        $stufe->setBezeichnung("demoName");
        $stufe->setFarbe('demoFarbe');
        $stufe->setStartalter(9);
        $stufe->setEndalter(23);
        $stufe->setCategorieId(42);

        $this->assertEquals(23, $stufe->getUid());
        $this->assertEquals('demoVerband', $stufe->getVerband());
        $this->assertEquals("demoName", $stufe->getBezeichnung());
        $this->assertEquals('demoFarbe', $stufe->getFarbe());
        $this->assertEquals(9, $stufe->getStartalter());
        $this->assertEquals(23, $stufe->getEndalter());
        $this->assertEquals(42, $stufe->getCategorieId());
        $this->assertEquals("<img src='https://kalender.scoutnet.de/2.0/images/23.gif' alt='demoName' />", $stufe->getImageURL());
    }
}

class TestUserModel extends TestCase {
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
