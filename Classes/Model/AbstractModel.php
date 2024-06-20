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

namespace ScoutNet\Api\Model;

abstract class AbstractModel
{
    /**
     * @var int|string|null
     */
    protected int|string|null $uid = null;

    /**
     * @return int|string|null
     */
    public function getUid(): int|string|null
    {
        return $this->uid;
    }

    /**
     * @param int|string|null $uid int
     */
    public function setUid(int|string|null $uid): void
    {
        $this->uid = $uid;
    }
}
