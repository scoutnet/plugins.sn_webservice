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

class User extends AbstractModel {
    const SEX_MALE = 'm';
    const SEX_FEMALE = 'w';
    const SEX_DIVERSE = 'd';


    /**
     * @var string
     * @validate NotEmpty
     * @validate StringLength(minimum=2, maximum=80)
     */
    protected string $username = '';

    /**
     * @var string
     * @validate NotEmpty
     * @validate StringLength(minimum=2, maximum=80)
     */
    protected ?string $firstName = '';

    /**
     * @var string
     * @validate NotEmpty
     * @validate StringLength(minimum=2, maximum=80)
     */
    protected ?string $lastName = '';

    /**
     * @var string
     * @validate NotEmpty
     * @validate StringLength(minimum=1, maximum=80)
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
        return trim($this->firstName) ? trim($this->firstName) : $this->getUsername();
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(?string $lastName): void
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
        $this->sex = $sex;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        // we use the class functions not the getters, because the firstname can be the username
        $full_name = $this->firstName . ' ' . $this->lastName;
        return trim($full_name) ? trim($full_name) : $this->getUsername();
    }

    /**
     * @return string
     */
    public function getLongName(): string
    {
        $full_name = $this->getFullName();
        if ($full_name != $this->getUsername()) {
            return $full_name . ' (' . $this->getUsername() . ')';
        }
        return $this->getUsername();
    }
}
