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

class EventModelTest extends TestCase {
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
        $this->assertEquals([], $event->getSections());
        $this->assertEquals([], $event->getCategories());
        $this->assertEquals(null, $event->getStructure());
        $this->assertEquals(null, $event->getChangedBy());
        $this->assertEquals(null, $event->getCreatedBy());
//        $this->assertEquals(null, $event->getCreatedAt());
//        $this->assertEquals(null, $event->getChangedAt());

        // derived values
        $this->assertEquals('', $event->getSectionImages());
    }

    public function testSetParameter() {
        $event = new Event();

        $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-09 00:00:00');
        $end = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-10 00:00:00');

        $stufe1_cat = new Category();
        $stufe1_cat->setUid(1);
        $stufe1_cat->setText('Section1');

        $stufe2_cat = new Category();
        $stufe2_cat->setUid(2);
        $stufe2_cat->setText('Section2');

        $stufe3_cat = new Category();
        $stufe3_cat->setUid(3);
        $stufe3_cat->setText('Section3');

        // stufen helper values
        $stufe1 = new Section();
        $stufe1->setUid(1);
        $stufe1->setBezeichnung('Section1');
        $stufe1->setCategory($stufe1_cat);

        $stufe2 = new Section();
        $stufe2->setUid(2);
        $stufe2->setBezeichnung('Section2');
        $stufe2->setCategory($stufe2_cat);

        $stufe3 = new Section();
        $stufe3->setUid(3);
        $stufe3->setBezeichnung('Section3');
        $stufe3->setCategory($stufe3_cat);


        $cat1 = new Category();
        $cat1->setUid(1);
        $cat1->setText('cat_1');

        $cat2 = new Category();
        $cat2->setUid(2);
        $cat2->setText('cat_2');

        $cat3 = new Category();
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


        $event->setSections([$stufe1, $stufe2]);
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
        $this->assertEquals('09', $event->getStartMonth());
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
        $this->assertEquals([$stufe1, $stufe2], $event->getSections());
        $this->assertEquals('<img src=\'https://kalender.scoutnet.de/2.0/images/1.gif\' alt=\'Section1\' /><img src=\'https://kalender.scoutnet.de/2.0/images/2.gif\' alt=\'Section2\' />', $event->getSectionImages());

        // add stufe
        $event->addSection($stufe3);
        $this->assertEquals([$stufe1, $stufe2, $stufe3], $event->getSections());
        $this->assertEquals([1 => $stufe1_cat, 2 => $stufe2_cat, 3 => $stufe3_cat], $event->getSectionCategories());


        // check Category
        $this->assertEquals([1 => $cat1, 2 => $cat2], $event->getCategories());

        // add Category
        $event->addCategory($cat3);
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
            'zip', 'location', 'urlText', 'url', 'description', 'structure', 'sections', 'categories'];

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
