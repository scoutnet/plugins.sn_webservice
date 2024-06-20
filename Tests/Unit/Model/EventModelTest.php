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

use DateTime;
use Error;
use PHPUnit\Framework\TestCase;
use ScoutNet\Api\Model\Category;
use ScoutNet\Api\Model\Event;
use ScoutNet\Api\Model\Section;
use ScoutNet\Api\Model\Structure;
use ScoutNet\Api\Model\User;

class EventModelTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        self::assertInstanceOf(Event::class, new Event('title', new DateTime('now')));
    }

    public function testDefaultParameters(): void
    {
        $event = new Event();
        $test_now = new DateTime('now');

        self::assertNull($event->getUid());
        self::assertEquals('new Event', $event->getTitle());
        self::assertEquals('', $event->getOrganizer());
        self::assertEquals('', $event->getTargetGroup());
        // will be 'now', so fuzzy check to be current timestamp, can break, if on minute boundary
        self::assertEquals($test_now->format('Y-m-d H:i'), $event->getStartDate()->format('Y-m-d H:i'));
        self::assertNull($event->getStartTime());
        self::assertNull($event->getEndDate());
        self::assertNull($event->getEndTime());
        self::assertEquals('', $event->getZip());
        self::assertEquals('', $event->getLocation());
        self::assertEquals('', $event->getUrlText());
        self::assertEquals('', $event->getUrl());
        self::assertEquals('', $event->getDescription());
        self::assertEquals([], $event->getSections());
        self::assertEquals([], $event->getCategories());
        try {
            // Structure needs to be set beforehand, otherwise we will get an error
            self::assertNull($event->getStructure());
            self::fail('Structure has default');
        } catch (Error $e) {
            self::assertEquals('Typed property ' . Event::class . '::$structure must not be accessed before initialization', $e->getMessage());
        }
        self::assertNull($event->getChangedBy());
        self::assertNull($event->getCreatedBy());
        // will be 'now', so fuzzy check to be current timestamp, can break, if on minute boundary
        self::assertEquals($test_now->format('Y-m-d H:i'), $event->getCreatedAt()->format('Y-m-d H:i'));
        self::assertNull($event->getChangedAt());

        // derived values
        self::assertEquals('', $event->getSectionImages());
    }

    public function testSetParameter(): void
    {
        $start = new DateTime('2001-09-09');
        $end = new DateTime('2001-09-10');

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

        $createdAt = new DateTime('2001-09-11 00:00:00');
        $changedAt = new DateTime('2001-09-12 00:00:00');

        $event = new Event('demoTitle', $start);

        // set values
        $event->setUid(23);
        $event->setTitle('demoTitle');
        $event->setOrganizer('demoOrganizer');
        $event->setTargetGroup('demoTargetGroup');

        $event->setStartDate($start);
        $event->setStartTime('10:00');
        $event->setEndDate($end);
        $event->setEndTime('23:00');

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
        self::assertEquals(23, $event->getUid());
        self::assertEquals('demoTitle', $event->getTitle());
        self::assertEquals('demoOrganizer', $event->getOrganizer());
        self::assertEquals('demoTargetGroup', $event->getTargetGroup());

        // time
        self::assertEquals($start, $event->getStartDate());
        self::assertEquals('10:00', $event->getStartTime());
        self::assertEquals($end, $event->getEndDate());
        self::assertEquals('23:00', $event->getEndTime());

        // derived time values
        self::assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-09 10:00:00'), $event->getStartTimestamp());
        self::assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-10 23:00:00'), $event->getEndTimestamp());

        // if startTime is null we get 00:00
        $event->setStartTime(null);
        self::assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-09 00:00:00'), $event->getStartTimestamp());

        // if endTime is null we get 00:00
        $event->setEndTime(null);
        self::assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-10 00:00:00'), $event->getEndTimestamp());

        // if endDate is null but endTime is set we get startDate endTime
        $event->setEndTime('23:00');
        $event->setEndDate(null);
        self::assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-09 23:00:00'), $event->getEndTimestamp());

        // if endDate and endTime is null we get startDate
        $event->setEndDate(null);
        $event->setEndTime(null);
        self::assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-09 00:00:00'), $event->getEndTimestamp());

        // show the EndDate only if it is set
        self::assertFalse($event->getShowEndDate());

        // show the EndDate if endDate is set
        $event->setEndDate(DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-10 00:00:00'));
        self::assertTrue($event->getShowEndDate());

        // do not show the EndDate if start == end
        $event->setEndDate($event->getStartDate());
        self::assertFalse($event->getShowEndDate());

        // show the EndTime if it not null
        self::assertFalse($event->getShowEndTime());
        $event->setEndTime('23:00');
        self::assertTrue($event->getShowEndTime());

        // ShowEndTime is set -> endDate or Time should be the same
        self::assertTrue($event->getShowEndDateOrTime());

        // check if allDay Event (no startTime is set)
        self::assertTrue($event->getAllDayEvent());

        $event->setStartTime('10:00');
        self::assertFalse($event->getAllDayEvent());

        // check if start Month/Year are correct
        self::assertEquals('09', $event->getStartMonth());
        self::assertEquals('2001', $event->getStartYear());

        // check location values
        self::assertEquals('demoZip', $event->getZip());
        self::assertEquals('demoLocation', $event->getLocation());
        self::assertEquals('demoUrlText', $event->getUrlText());
        self::assertEquals('demoUrl', $event->getUrl());
        self::assertEquals('demoDescription', $event->getDescription());

        // check if we show details
        self::assertTrue($event->getShowDetails());

        $properties = ['description', 'zip', 'location', 'organizer', 'targetGroup', 'url'];

        // do not show, if all are set to ''
        foreach ($properties as $reset_property) {
            $setter = 'set' . ucfirst($reset_property);
            $event->$setter('');
        }
        self::assertFalse($event->getShowDetails());

        // but if one is set, show it
        foreach ($properties as $property) {
            foreach ($properties as $reset_property) {
                $setter = 'set' . ucfirst($reset_property);
                $event->$setter('');
            }
            $setter = 'set' . ucfirst($property);
            $event->$setter('demo' . ucfirst($property));

            self::assertTrue($event->getShowDetails());
        }

        // check stufe
        self::assertEquals([$stufe1, $stufe2], $event->getSections());
        self::assertEquals('<img src=\'https://kalender.scoutnet.de/2.0/images/1.gif\' alt=\'Section1\' /><img src=\'https://kalender.scoutnet.de/2.0/images/2.gif\' alt=\'Section2\' />', $event->getSectionImages());

        // add stufe
        $event->addSection($stufe3);
        self::assertEquals([$stufe1, $stufe2, $stufe3], $event->getSections());
        self::assertEquals([1 => $stufe1_cat, 2 => $stufe2_cat, 3 => $stufe3_cat], $event->getSectionCategories());

        // check Category
        self::assertEquals([1 => $cat1, 2 => $cat2], $event->getCategories());

        // add Category
        $event->addCategory($cat3);
        self::assertEquals([1 => $cat1, 2 => $cat2, 3 => $cat3], $event->getCategories());

        self::assertEquals($structure, $event->getStructure());

        self::assertEquals($changedBy, $event->getChangedBy());
        self::assertEquals($createdBy, $event->getCreatedBy());
        self::assertEquals($createdAt, $event->getCreatedAt());
        self::assertEquals($changedAt, $event->getChangedAt());

        // check if these values are correctly copied
        $copiedProperties = ['title', 'organizer', 'targetGroup', 'startDate', 'startTime', 'endDate', 'endTime',
            'zip', 'location', 'urlText', 'url', 'description', 'structure', 'sections', 'categories'];

        $test_now = new DateTime('now');

        $event_copy = new Event();
        $event_copy->copyProperties($event);

        foreach ($copiedProperties as $property) {
            $getter = 'get' . ucfirst($property);
            self::assertEquals($event->$getter(), $event_copy->$getter());
        }

        // check that some others are not copied
        self::assertNull($event_copy->getUid());
        self::assertNull($event_copy->getChangedBy());
        self::assertNull($event_copy->getCreatedBy());
        // will be 'now', so fuzzy check to be current timestamp, can break, if on minute boundary
        self::assertEquals($test_now->format('Y-m-d H:i'), $event_copy->getCreatedAt()->format('Y-m-d H:i'));
        self::assertNull($event_copy->getChangedAt());
    }
}
