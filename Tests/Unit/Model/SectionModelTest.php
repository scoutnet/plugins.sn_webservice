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

class SectionModelTest extends TestCase {
    public function testCanBeCreated() {
        $this->assertInstanceOf(Section::class, new Section());
    }

    public function testDefaultParameters() {
        $stufe = new Section();

        $this->assertEquals(null, $stufe->getUid());
        $this->assertEquals(null, $stufe->getVerband());
        $this->assertEquals(null, $stufe->getBezeichnung());
        $this->assertEquals(null, $stufe->getFarbe());
        $this->assertEquals(-1, $stufe->getStartalter());
        $this->assertEquals(-1, $stufe->getEndalter());
        $this->assertEquals(null, $stufe->getCategory());
    }

    public function testSetParameter() {
        $stufe = new Section();

        $stufe->setUid(23);
        $stufe->setVerband('demoVerband');
        $stufe->setBezeichnung("demoName");
        $stufe->setFarbe('demoFarbe');
        $stufe->setStartalter(9);
        $stufe->setEndalter(23);
//        $stufe->setCategorieId(42);

        $this->assertEquals(23, $stufe->getUid());
        $this->assertEquals('demoVerband', $stufe->getVerband());
        $this->assertEquals("demoName", $stufe->getBezeichnung());
        $this->assertEquals('demoFarbe', $stufe->getFarbe());
        $this->assertEquals(9, $stufe->getStartalter());
        $this->assertEquals(23, $stufe->getEndalter());
//        $this->assertEquals(42, $stufe->getCategorieId());
        $this->assertEquals("<img src='https://kalender.scoutnet.de/2.0/images/23.gif' alt='demoName' />", $stufe->getImageURL());
    }
}
