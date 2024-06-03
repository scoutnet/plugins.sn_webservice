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

namespace ScoutNet\Api\Models;

class Permission
{
    public const AUTH_NO_RIGHT = 1;
    public const AUTH_WRITE_ALLOWED = 0;
    public const AUTH_REQUESTED = 0;
    public const AUTH_REQUEST_PENDING = 2;

    protected $state = self::AUTH_NO_RIGHT;
    protected $text = '';
    protected $type = '';

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }

    public function hasWritePermissionsPending()
    {
        return $this->state == self::AUTH_REQUEST_PENDING;
    }

    public function hasWritePermissions()
    {
        return $this->state == self::AUTH_WRITE_ALLOWED;
    }

    /**
     * @return mixed|string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed|string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed|string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
