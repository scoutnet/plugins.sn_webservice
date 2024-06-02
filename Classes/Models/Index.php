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

class Index extends AbstractModel {
    /**
     * @var int internal number of Index Element
     */
	protected $number = null;

    /**
     * @var string
     */
	protected $ebene = null;

    /**
     * @var string
     */
	protected $name = null;

	/**
     * @var string
     */
	protected $ort = null;
    /**
     * @var string
     */
	protected $plz = null;

    /**
     * @var string
     */
	protected $url = null;

    /**
     * @var float
     */
	protected $latitude = null;

    /**
     * @var float
     */
	protected $longitude = null;

    /**
     * @var int
     */
	protected $parent_id = null;

    /**
     * @var \ScoutNet\Api\Models\Index[]
     */
    protected $children = [];

    /**
     * @return int
     */
    public function getNumber() {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber($number) {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getEbene() {
        return $this->ebene;
    }

    /**
     * @param string $ebene
     */
    public function setEbene($ebene) {
        $this->ebene = $ebene;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getOrt() {
        return $this->ort;
    }

    /**
     * @param string $ort
     */
    public function setOrt($ort) {
        $this->ort = $ort;
    }

    /**
     * @return string
     */
    public function getPlz() {
        return $this->plz;
    }

    /**
     * @param string $plz
     */
    public function setPlz($plz) {
        $this->plz = $plz;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * @return float
     */
    public function getLatitude() {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude($latitude) {
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude() {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     */
    public function setLongitude($longitude) {
        $this->longitude = $longitude;
    }

    /**
     * @return int
     */
    public function getParentId() {
        return $this->parent_id;
    }

    /**
     * @param int $parent_id
     */
    public function setParentId($parent_id) {
        $this->parent_id = $parent_id;
    }

    /**
     * @return Index[]
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * @param Index[] $children
     */
    public function setChildren($children) {
        $this->children = $children;
    }

    public function addChild(&$child) {
	    $this->children[] = $child;
    }

}
