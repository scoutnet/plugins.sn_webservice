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

namespace ScoutNet\Api\Tests\Unit\Helpers\Helpers;

use PHPUnit\Framework\TestCase;
use ScoutNet\Api\Helpers\CacheHelper;
use ScoutNet\Api\Helpers\ConverterHelper;
use ScoutNet\Api\Model\Category;
use ScoutNet\Api\Model\Event;
use ScoutNet\Api\Model\Index;
use ScoutNet\Api\Model\Permission;
use ScoutNet\Api\Model\Section;
use ScoutNet\Api\Model\Structure;
use ScoutNet\Api\Model\Stufe;
use ScoutNet\Api\Model\User;

class ConverterHelperTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        self::assertInstanceOf(ConverterHelper::class, new ConverterHelper());
    }

    public function testConvertCategoryValidArray(): void
    {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $expected_Category = new Category();

        $expected_Category->setUid(23);
        $expected_Category->setText('Category 1');

        $array = ['ID' => 23, 'Text' => 'Category 1'];
        $is_Category = $converter->convertApiToCategory($array);
        $cached_Category = $cache->get(Category::class, 23);

        self::assertEquals($expected_Category, $is_Category);
        self::assertEquals($expected_Category, $cached_Category);
    }

    public function testConvertCategoryEmptyArray(): void
    {
        $converter = new ConverterHelper();

        $expected_Category = new Category();

        $expected_Category->setUid(-1);
        $expected_Category->setText('');

        $array = [];
        $is_Category = $converter->convertApiToCategory($array);

        self::assertEquals($expected_Category, $is_Category);
    }

    public function testConvertPermissionValidArray(): void
    {
        $converter = new ConverterHelper();

        $expected_permission = new Permission();

        $expected_permission->setState(Permission::AUTH_WRITE_ALLOWED);
        $expected_permission->setText('Permission 1');
        $expected_permission->setType('Type 1');

        $array = ['code' => Permission::AUTH_WRITE_ALLOWED, 'text' => 'Permission 1', 'type' => 'Type 1'];
        $is_permission = $converter->convertApiToPermission($array);

        self::assertEquals($expected_permission, $is_permission);
    }

    public function testConvertPermissionEmptyArray(): void
    {
        $converter = new ConverterHelper();

        $expected_permission = new Permission();

        $expected_permission->setState(Permission::AUTH_NO_RIGHT);
        $expected_permission->setText('');
        $expected_permission->setType('');

        $array = [];
        $is_permission = $converter->convertApiToPermission($array);

        self::assertEquals($expected_permission, $is_permission);
    }

    public function testConvertStructureValidArray(): void
    {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $structure = new Structure();

        $structure->setUid(23);

        $structure->setLevel('demoEbene');
        $structure->setName('demoName');
        $structure->setVerband('demoVerband');
        $structure->setIdent('demoIdent');
        $structure->setLevelId(23);

        $cat1 = new Category();
        $cat1->setUid(1);
        $cat1->setText('cat1');

        $cat2 = new Category();
        $cat2->setUid(2);
        $cat2->setText('cat2');

        $cat3 = new Category();
        $cat3->setUid(3);
        $cat3->setText('cat3');

        $cat4 = new Category();
        $cat4->setUid(4);
        $cat4->setText('cat4');

        $structure->setUsedCategories([1 => $cat1, 2 => $cat2]);
        $structure->setForcedCategories(['section1' => [3 => $cat3], 'section2' => [4 => $cat4]]);

        $array = [
            'ID' => 23,
            'Ebene' => 'demoEbene',
            'Name' => 'demoName',
            'Verband' => 'demoVerband',
            'Ident' => 'demoIdent',
            'Ebene_Id' => 23,
            'Used_Kategories' => [
                1 => 'cat1',
                2 => 'cat2',
            ],
            'Forced_Kategories' => [
                'section1' => [3 => 'cat3'],
                'section2' => [4 => 'cat4'],
            ],
        ];
        $is_structure = $converter->convertApiToStructure($array);
        $cached_structure = $cache->get(Structure::class, 23);

        self::assertEquals($structure, $is_structure);
        self::assertEquals($structure, $cached_structure);
    }

    public function testConvertStructureValidArrayNoCategories(): void
    {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $structure = new Structure();

        $structure->setUid(23);

        $structure->setLevel('demoEbene');
        $structure->setName('demoName');
        $structure->setVerband('demoVerband');
        $structure->setIdent('demoIdent');
        $structure->setLevelId(23);

        $structure->setUsedCategories([]);
        $structure->setForcedCategories(['section1' => [], 'section2' => []]);

        $array = [
            'ID' => 23,
            'Ebene' => 'demoEbene',
            'Name' => 'demoName',
            'Verband' => 'demoVerband',
            'Ident' => 'demoIdent',
            'Ebene_Id' => 23,
            'Used_Kategories' => [
            ],
            'Forced_Kategories' => [
                'section1' => '',
                'section2' => '',
            ],
        ];
        $is_structure = $converter->convertApiToStructure($array);
        $cached_structure = $cache->get(Structure::class, 23);

        self::assertEquals($structure, $is_structure);
        self::assertEquals($structure, $cached_structure);
    }

    public function testConvertStructureEmptyArray(): void
    {
        $converter = new ConverterHelper();

        $structure = new Structure();

        $structure->setUid(-1);
        $structure->setLevel('');
        $structure->setName('');
        $structure->setVerband('');
        $structure->setIdent('');
        $structure->setLevelId(0);
        $structure->setUsedCategories([]);
        $structure->setForcedCategories([]);

        $array = [];
        $is_structure = $converter->convertApiToStructure($array);

        self::assertEquals($structure, $is_structure);
    }

    public function testConvertSectionValidArray(): void
    {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $cat = new Category();
        $cat->setUid(1);
        $cat->setText('Sonstiges');

        $expected_stufe = new Section();

        $expected_stufe->setUid(23);
        $expected_stufe->setVerband('demoVerband');
        $expected_stufe->setBezeichnung('Stufe 1');
        $expected_stufe->setFarbe('#ffeeff');
        $expected_stufe->setStartalter(9);
        $expected_stufe->setEndalter(11);
        // TODO: make work
        //        $expected_stufe->setCategory($cat);

        $array = [
            'id' => 23,
            'verband' => 'demoVerband',
            'bezeichnung' => 'Stufe 1',
            'farbe' => '#ffeeff',
            'startalter' => 9,
            'endalter' => 11,
            'Keywords_ID' => 1,
        ];
        $is_stufe = $converter->convertApiToSection($array);
        $cached_stufe = $cache->get(Section::class, 23);

        self::assertEquals($expected_stufe, $is_stufe);
        self::assertEquals($expected_stufe, $cached_stufe);
    }

    public function testConvertSectionEmptyArray(): void
    {
        $converter = new ConverterHelper();

        $expected_stufe = new Section();

        $expected_stufe->setUid(-1);
        $expected_stufe->setVerband('');
        $expected_stufe->setBezeichnung('');
        $expected_stufe->setFarbe('');
        $expected_stufe->setStartalter(-1);
        $expected_stufe->setEndalter(-1);
        // TODO: make work
        //        $expected_stufe->setCategoryId(-1);

        $array = [];
        $is_stufe = $converter->convertApiToSection($array);

        self::assertEquals($expected_stufe, $is_stufe);
    }

    public function testConvertUserValidArray(): void
    {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $expected_user = new User();

        $expected_user->setUid('demoUsername');
        $expected_user->setUsername('demoUsername');
        $expected_user->setFirstName('demoFirstName');
        $expected_user->setLastName('demoLastName');
        $expected_user->setSex(User::SEX_FEMALE);

        $array = [
            'userid' => 'demoUsername',
            'firstname' => 'demoFirstName',
            'surname' => 'demoLastName',
            'sex' => 'w',
        ];

        $is_user = $converter->convertApiToUser($array);
        $cached_user = $cache->get(User::class, 'demoUsername');

        self::assertEquals($expected_user, $is_user);
        self::assertEquals($expected_user, $cached_user);
    }

    public function testConvertUserEmptyArray(): void
    {
        $converter = new ConverterHelper();

        $expected_user = new User();

        $expected_user->setUid(-1);
        $expected_user->setUsername('');
        $expected_user->setFirstName('');
        $expected_user->setLastName('');
        $expected_user->setSex('');

        $array = [];
        $is_user = $converter->convertApiToUser($array);

        self::assertEquals($expected_user, $is_user);
    }

    public function testConvertEventValidArray(): void
    {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $structure = new Structure();
        $structure->setUid(23);

        $changedBy = new User();
        $changedBy->setUid('user1');

        $createdBy = new User();
        $createdBy->setUid('user2');

        $createdAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-11 12:23:00');
        $changedAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-12 14:42:00');

        $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-09 00:00:00');
        $endDate = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-10 00:00:00');

        $cat = new Category();
        $cat->setUid(1);
        $cat->setText('Stufe');

        $stufe = new Section();
        $stufe->setUid(1);
        $stufe->setBezeichnung('Stufe');
        $stufe->setCategory($cat);

        $expected_event = new Event();

        $expected_event->setUid(23);
        $expected_event->setTitle('demoTitle');
        $expected_event->setOrganizer('demoOrganizer');
        $expected_event->setTargetGroup('demoTargetGroup');

        $expected_event->setStartDate($startDate);
        $expected_event->setStartTime('10:00:00');
        $expected_event->setEndDate($endDate);
        $expected_event->setEndTime('23:00:00');
        $expected_event->setZip('12345');
        $expected_event->setLocation('demoLocation');
        $expected_event->setUrlText('demoUrlText');
        $expected_event->setUrl('http://demoUrl');
        $expected_event->setDescription('demoDescription');
        $expected_event->setChangedAt($changedAt);
        $expected_event->setCreatedAt($createdAt);
        $expected_event->setCategories([1 => $cat]);

        // without cache this will be empty
        $expected_event->setChangedBy(null);
        $expected_event->setCreatedBy(null);
        //        $expected_event->setStructure(null);
        $expected_event->setStufen([]);

        $array = [
            'ID' => 23,
            'UID' => 23,
            'SSID' => '23',
            'Title' => 'demoTitle',
            'Organizer' => 'demoOrganizer',
            'Target_Group' => 'demoTargetGroup',
            'Start' => '1000029600',
            'End' => '1000162800',
            'All_Day' => false,
            'ZIP' => '12345',
            'Location' => 'demoLocation',
            'URL_Text' => 'demoUrlText',
            'URL' => 'http://demoUrl',
            'Description' => 'demoDescription',
            'Stufen' => [1],
            'Keywords' => ['1' => 'Stufe'],
            'Kalender' => '23',
            'Last_Modified_By' => 'user1',
            'Last_Modified_At' => '1000305720',
            'Created_By' => 'user2',
            'Created_At' => '1000210980',
        ];

        // without cache set
        $is_event = $converter->convertApiToEvent($array);
        $cached_event = $cache->get(Event::class, 23);

        self::assertEquals($expected_event, $is_event);
        self::assertEquals($expected_event, $cached_event);

        // cache the elements
        $cache->add($structure);
        $cache->add($changedBy);
        $cache->add($createdBy);
        $cache->add($stufe);
        $cache->add($cat);

        $expected_event->setChangedBy($changedBy);
        $expected_event->setCreatedBy($createdBy);
        $expected_event->setStructure($structure);
        $expected_event->setStufen([$stufe]);

        // with cache set
        $is_event = $converter->convertApiToEvent($array);
        $cached_event = $cache->get(Event::class, 23);

        self::assertEquals($expected_event, $is_event);
        self::assertEquals($expected_event, $cached_event);
    }

    public function testConvertEventEmptyArray(): void
    {
        $converter = new ConverterHelper();

        $expected_event = new Event();

        $expected_event->setUid(-1);
        $expected_event->setTitle('');
        $expected_event->setOrganizer('');
        $expected_event->setTargetGroup('');

        //        $expected_event->setStartDate(null);
        $expected_event->setStartTime(null);
        $expected_event->setEndDate(null);
        $expected_event->setEndTime(null);
        $expected_event->setZip('');
        $expected_event->setLocation('');
        $expected_event->setUrlText('');
        $expected_event->setUrl('');
        $expected_event->setDescription('');
        $expected_event->setChangedAt(null);
        //        $expected_event->setCreatedAt(null);
        $expected_event->setChangedBy(null);
        $expected_event->setCreatedBy(null);
        //        $expected_event->setStructure(null);

        $array = [];
        $is_event = $converter->convertApiToEvent($array);

        // StartDate and Created are 'now' so copy those over
        $expected_event->setStartDate($is_event->getStartDate());
        $expected_event->setCreatedAt($is_event->getCreatedAt());

        self::assertEquals($expected_event, $is_event);
    }

    public function testConvertEventToApi(): void
    {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $structure = new Structure();
        $structure->setUid(23);

        $changedBy = new User();
        $changedBy->setUid('user1');

        $createdBy = new User();
        $createdBy->setUid('user2');

        $createdAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-11 12:23:00');
        $changedAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-12 14:42:00');

        $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-09 00:00:00');
        $endDate = \DateTime::createFromFormat('Y-m-d H:i:s', '2001-09-10 00:00:00');

        $cat = new Category();
        $cat->setUid(1);
        $cat->setText('Stufe');

        $stufe = new Section();
        $stufe->setUid(1);
        $stufe->setBezeichnung('Stufe');
        $stufe->setCategory($cat);

        $custom_cat = new Category();
        $custom_cat->setUid(null);
        $custom_cat->setText('Custom1');

        $event = new Event();

        $event->setUid(23);
        $event->setTitle('demoTitle');
        $event->setOrganizer('demoOrganizer');
        $event->setTargetGroup('demoTargetGroup');

        $event->setStartDate($startDate);
        $event->setStartTime('10:00:00');
        $event->setEndDate($endDate);
        $event->setEndTime('23:00:00');
        $event->setZip('12345');
        $event->setLocation('demoLocation');
        $event->setUrlText('demoUrlText');
        $event->setUrl('http://demoUrl');
        $event->setDescription('demoDescription');
        $event->setChangedAt($changedAt);
        $event->setCreatedAt($createdAt);
        $event->setCategories([1 => $cat, -2 => $custom_cat]);
        $event->setChangedBy($changedBy);
        $event->setCreatedBy($createdBy);
        $event->setStructure($structure);
        $event->setStufen([$stufe]);

        $expected_array = [
            'ID' => 23,
            'UID' => 23,
            'SSID' => 23,
            'Title' => 'demoTitle',
            'Organizer' => 'demoOrganizer',
            'Target_Group' => 'demoTargetGroup',
            'Start' => '1000029600',
            'End' => '1000162800',
            'All_Day' => false,
            'ZIP' => '12345',
            'Location' => 'demoLocation',
            'URL_Text' => 'demoUrlText',
            'URL' => 'http://demoUrl',
            'Description' => 'demoDescription',
            'Stufen' => [1],
            'Keywords' => ['1' => 'Stufe'],
            'Kalender' => 23,
            'Last_Modified_By' => 'user1',
            'Last_Modified_At' => 1000305720,
            'Created_By' => 'user2',
            'Created_At' => 1000210980,
            'Custom_Keywords' => ['Custom1'],
        ];

        // without cache set
        $is_array = $converter->convertEventToApi($event);
        self::assertEquals($expected_array, $is_array);
    }

    public function testConverIndexValidArray(): void
    {
        $cache = new CacheHelper();
        $converter = new ConverterHelper($cache);

        $parent = new Index();
        $parent->setUid(42);
        $parent->setChildren([]);

        // check if parent get child set correct
        $cache->add($parent);

        $expected_index = new Index();

        $expected_index->setUid(23);
        $expected_index->setNumber('10/10/10');
        $expected_index->setEbene('demoEbene');
        $expected_index->setName('demoName');
        $expected_index->setOrt('demoOrt');
        $expected_index->setPlz('12345');
        $expected_index->setUrl('http://demoUrl');
        $expected_index->setLatitude(50.0);
        $expected_index->setLongitude(6.0);
        $expected_index->setParentId(42);
        $expected_index->setChildren([]);

        $array = [
            'id' => 23,
            'number' => '10/10/10',
            'ebene' => 'demoEbene',
            'name' => 'demoName',
            'ort' => 'demoOrt',
            'plz' => '12345',
            'url' => 'http://demoUrl',
            'latitude' => 50.0,
            'longitude' => 6.0,
            'parent_id' => 42,
        ];

        $is_index = $converter->convertApiToIndex($array);
        $cached_index = $cache->get(Index::class, 23);

        self::assertEquals($expected_index, $is_index);
        self::assertEquals($expected_index, $cached_index);

        // parent is set
        self::assertEquals([$is_index], $parent->getChildren());
    }

    public function testConvertIndexEmptyArray(): void
    {
        $converter = new ConverterHelper();

        $expected_index = new Index();

        $expected_index->setUid(-1);
        $expected_index->setNumber('');
        $expected_index->setEbene('');
        $expected_index->setName('');
        $expected_index->setOrt('');
        $expected_index->setPlz('');
        $expected_index->setUrl('');
        $expected_index->setLatitude(0.0);
        $expected_index->setLongitude(0.0);
        $expected_index->setParentId(null);
        $expected_index->setChildren([]);

        $array = [];
        $is_index = $converter->convertApiToIndex($array);

        self::assertEquals($expected_index, $is_index);
    }
}
