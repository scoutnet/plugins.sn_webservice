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
use ScoutNet\Api\Models\Section;

class SectionModelTest extends TestCase
{
    public function testCanBeCreated()
    {
        self::assertInstanceOf(Section::class, new Section());
    }

    public function testDefaultParameters()
    {
        $stufe = new Section();

        self::assertNull($stufe->getUid());
        self::assertNull($stufe->getVerband());
        self::assertNull($stufe->getBezeichnung());
        self::assertNull($stufe->getFarbe());
        self::assertEquals(-1, $stufe->getStartalter());
        self::assertEquals(-1, $stufe->getEndalter());
        self::assertNull($stufe->getCategory());
    }

    public function testSetParameter()
    {
        $stufe = new Section();

        $stufe->setUid(23);
        $stufe->setVerband('demoVerband');
        $stufe->setBezeichnung('demoName');
        $stufe->setFarbe('demoFarbe');
        $stufe->setStartalter(9);
        $stufe->setEndalter(23);
        //        $stufe->setCategorieId(42);

        self::assertEquals(23, $stufe->getUid());
        self::assertEquals('demoVerband', $stufe->getVerband());
        self::assertEquals('demoName', $stufe->getBezeichnung());
        self::assertEquals('demoFarbe', $stufe->getFarbe());
        self::assertEquals(9, $stufe->getStartalter());
        self::assertEquals(23, $stufe->getEndalter());
        //        $this->assertEquals(42, $stufe->getCategorieId());
        self::assertEquals("<img src='https://kalender.scoutnet.de/2.0/images/23.gif' alt='demoName' />", $stufe->getImageURL());
    }
}
