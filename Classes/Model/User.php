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

class User extends AbstractModel
{
    public const SEX_MALE = 'm';
    public const SEX_FEMALE = 'w';
    public const SEX_DIVERSE = 'd';

    /**
     * @var string
     */
    protected string $username = '';

    /**
     * @var string
     */
    protected string $firstName = '';

    /**
     * @var string
     */
    protected string $lastName = '';

    /**
     * @var string
     */
    protected string $sex = '';

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getFirstName(): string
    {
        return trim($this->firstName) ?: $this->getUsername();
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getSex(): string
    {
        return $this->sex;
    }

    /**
     * @param string $sex
     */
    public function setSex(string $sex): void
    {
        // only save, if valid
        if (in_array($sex, [self::SEX_DIVERSE, self::SEX_MALE, self::SEX_FEMALE])) {
            $this->sex = $sex;
        }
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        // we use the class functions not the getters, because the firstname can be the username
        $full_name = $this->firstName . ' ' . $this->lastName;
        return trim($full_name) ?: $this->getUsername();
    }

    /**
     * @return string
     */
    public function getLongName(): string
    {
        $full_name = $this->getFullName();
        if ($full_name !== $this->getUsername()) {
            return $full_name . ' (' . $this->getUsername() . ')';
        }
        return $this->getUsername();
    }
}
