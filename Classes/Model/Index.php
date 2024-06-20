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

class Index extends AbstractModel
{
    /**
     * @var string internal number of Index Element
     */
    protected string $number = '';

    /**
     * @var string
     */
    protected string $ebene = '';

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var string
     */
    protected string $ort = '';
    /**
     * @var string
     */
    protected string $plz = '';

    /**
     * @var string
     */
    protected string $url = '';

    /**
     * @var float
     */
    protected float $latitude = 0.0;

    /**
     * @var float
     */
    protected float $longitude = 0.0;

    /**
     * @var int|null
     */
    protected ?int $parent_id = null;

    /**
     * @var Index[]
     */
    protected array $children = [];

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getEbene(): string
    {
        return $this->ebene;
    }

    /**
     * @param string $ebene
     */
    public function setEbene(string $ebene): void
    {
        $this->ebene = $ebene;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
    public function getOrt(): string
    {
        return $this->ort;
    }

    /**
     * @param string $ort
     */
    public function setOrt(string $ort): void
    {
        $this->ort = $ort;
    }

    /**
     * @return string
     */
    public function getPlz(): string
    {
        return $this->plz;
    }

    /**
     * @param string $plz
     */
    public function setPlz(string $plz): void
    {
        $this->plz = $plz;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     */
    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
    }

    /**
     * @return int
     */
    public function getParentId(): ?int
    {
        return $this->parent_id;
    }

    /**
     * @param int $parent_id
     */
    public function setParentId(?int $parent_id): void
    {
        $this->parent_id = $parent_id;
    }

    /**
     * @return Index[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param Index[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function addChild(Index $child): void
    {
        $this->children[] = $child;
    }
}
