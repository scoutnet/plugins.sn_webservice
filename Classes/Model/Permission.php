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

class Permission
{
    public const AUTH_NO_RIGHT = 1;
    public const AUTH_WRITE_ALLOWED = 0;
    public const AUTH_REQUESTED = 0;
    public const AUTH_REQUEST_PENDING = 2;

    protected int $state = self::AUTH_NO_RIGHT;
    protected string $text = '';
    protected string $type = '';

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function hasWritePermissionsPending(): bool
    {
        return $this->state === self::AUTH_REQUEST_PENDING;
    }

    public function hasWritePermissions(): bool
    {
        return $this->state === self::AUTH_WRITE_ALLOWED;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
