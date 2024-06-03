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
use ScoutNet\Api\Models\Structure;

class StructureModelTest extends TestCase
{
    public function testCanBeCreated()
    {
        self::assertInstanceOf(Structure::class, new Structure());
    }

    public function testDefaultParameters()
    {
        $structure = new Structure();

        self::assertNull($structure->getUid());
        self::assertNull($structure->getEbene());
        self::assertNull($structure->getName());
        self::assertNull($structure->getVerband());
        self::assertNull($structure->getIdent());
        self::assertEquals(-1, $structure->getEbeneId());
        self::assertEquals([], $structure->getUsedCategories());
        self::assertEquals([], $structure->getForcedCategories());
    }

    public function testSetParameter()
    {
        $structure = new Structure();

        $structure->setUid(23);
        $structure->setEbene('demoEbene');
        $structure->setName('demoName');
        $structure->setVerband('demoVerband');
        $structure->setIdent('demoIdent');
        $structure->setEbeneId(23);
        $structure->setUsedCategories(['categorie_1', 'categorie_2']);
        $structure->setForcedCategories(['categorie_3', 'categorie_4']);

        self::assertEquals(23, $structure->getUid());
        self::assertEquals('demoEbene', $structure->getEbene());
        self::assertEquals('demoName', $structure->getName());
        self::assertEquals('demoVerband', $structure->getVerband());
        self::assertEquals('demoIdent', $structure->getIdent());
        self::assertEquals(23, $structure->getEbeneId());
        self::assertEquals(['categorie_1', 'categorie_2'], $structure->getUsedCategories());
        self::assertEquals(['categorie_3', 'categorie_4'], $structure->getForcedCategories());
        self::assertEquals('demoEbene demoName', $structure->getLongName());

        // if ebene < 4 do not show name
        $structure->setEbeneId(1);
        self::assertEquals('demoEbene', $structure->getLongName());
    }
}
