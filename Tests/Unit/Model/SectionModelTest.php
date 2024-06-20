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

namespace ScoutNet\Api\Tests\Unit\Helpers\Helpers\Helpers\Helpers\Helpers\Unit\Model;

use PHPUnit\Framework\TestCase;
use ScoutNet\Api\Model\Category;
use ScoutNet\Api\Model\Section;

class SectionModelTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        self::assertInstanceOf(Section::class, new Section());
    }

    public function testDefaultParameters(): void
    {
        $stufe = new Section();

        self::assertNull($stufe->getUid());
        self::assertEquals('', $stufe->getVerband());
        self::assertEquals('', $stufe->getBezeichnung());
        self::assertEquals('', $stufe->getFarbe());
        self::assertEquals(0, $stufe->getStartalter());
        self::assertEquals(0, $stufe->getEndalter());
        self::assertNull($stufe->getCategory());
    }

    public function testSetParameter(): void
    {
        $cat1 = new Category();
        $cat1->setText('Section 1');
        $cat1->setUid(1);
        $stufe = new Section();

        $stufe->setUid(23);
        $stufe->setVerband('demoVerband');
        $stufe->setBezeichnung('demoName');
        $stufe->setFarbe('demoFarbe');
        $stufe->setStartalter(9);
        $stufe->setEndalter(23);
        $stufe->setCategory($cat1);

        self::assertEquals(23, $stufe->getUid());
        self::assertEquals('demoVerband', $stufe->getVerband());
        self::assertEquals('demoName', $stufe->getBezeichnung());
        self::assertEquals('demoFarbe', $stufe->getFarbe());
        self::assertEquals(9, $stufe->getStartalter());
        self::assertEquals(23, $stufe->getEndalter());
        self::assertEquals($cat1, $stufe->getCategory());
        self::assertEquals(1, $stufe->getCategoryId());
        self::assertEquals("<img src='https://kalender.scoutnet.de/2.0/images/23.gif' alt='demoName' />", $stufe->getImageURL());
    }
}
