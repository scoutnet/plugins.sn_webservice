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

class Structure extends AbstractModel {
    /**
     * @var string
     */
    protected string $level;
    /**
     * @var string
     */
    protected string $name;
    /**
     * @var string
     */
    protected string $verband;
    /**
     * @var string
     */
    protected string $ident;
    /**
     * @var int
     */
    protected int $levelId;
    /**
     * @var array
     */
    protected array $usedCategories;
    /**
     * @var array
     */
    protected array $forcedCategories;

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level ?? '';
    }

    /**
     * @param string $level
     */
    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getEbene(): string
    {
        return $this->getLevel();
    }

    /**
     * @param string $ebene
     * @deprecated
     */
    public function setEbene(string $ebene): void
    {
        $this->setLevel($ebene);
    }

    /**
     * @return string
     */
    public function getVerband(): string
    {
        return $this->verband??'';
    }

    /**
     * @param string $verband
     */
    public function setVerband(string $verband): void
    {
        $this->verband = $verband;
    }

    /**
     * @return string
     */
    public function getIdent(): string
    {
        return $this->ident??'';
    }

    /**
     * @param string $ident
     */
    public function setIdent(string $ident): void
    {
        $this->ident = $ident;
    }

    /**
     * @return int
     */
    public function getLevelId(): int
    {
        return $this->levelId ?? -1;
    }

    /**
     * @param int $levelId
     */
    public function setLevelId(int $levelId): void
    {
        $this->levelId = $levelId;
    }

    /**
     * @return int
     * @deprecated
     */
    public function getEbeneId(): int
    {
        return $this->getLevelId();
    }

    /**
     * @param int $ebeneId
     * @deprecated
     */
    public function setEbeneId(int $ebeneId): void
    {
        $this->setLevelId($ebeneId);
    }

    /**
     * @return array
     */
    public function getUsedCategories(): array
    {
        return $this->usedCategories ?? [];
    }

    /**
     * @param array $usedCategories
     */
    public function setUsedCategories(array $usedCategories): void
    {
        $this->usedCategories = $usedCategories;
    }

    /**
     * @return array
     */
    public function getForcedCategories(): array
    {
        return $this->forcedCategories ?? [];
    }

    /**
     * @param array $forcedCategories
     */
    public function setForcedCategories(array $forcedCategories): void
    {
        $this->forcedCategories = $forcedCategories;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? '';
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getLongName(): string
    {
        return $this->getLevel() . (($this->getLevelId() >= 7) ? (' ' . $this->getName()) : '');
    }

    /**
     * @return string
     * @deprecated
     */
    public function get_long_Name() {
        return $this->getLongName();
    }

    /**
     * @return string
     * @deprecated
     */
    public function get_Name(): string
    {
        return (string)$this->getLevel() . (($this->getLevelId() >= 7) ? '&nbsp;' . $this->getName() : '');
    }
}
