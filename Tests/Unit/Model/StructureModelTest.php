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

class StructureModelTest extends TestCase {
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
        $this->assertEquals(-1, $structure->getEbeneId());
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
