<?php
namespace ScoutNet\Api\Models;
/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Stefan "Mütze" Horst <muetze@scoutnet.de>, ScoutNet
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class Stufe {
    private $array = [];

	function __construct( $array ){
	    $this->array = $array;

        $this->uid = $array['id'];
        $this->verband = $array['verband'];
        $this->bezeichnung = $array['bezeichnung'];
        $this->farbe = $array['farbe'];
        $this->startalter = intval($array['startalter']);
        $this->endalter = intval($array['endalter']);
        $this->categorieId = $array['Keywords_ID'];
	}


    /**
     * @return string
     * @deprecated
     */
	public function get_Image_URL(){
		return (string) $this->getImageURL();
	}

    /**
     * @var Integer
     */
	protected $uid;

    /**
     * @var String
     */
    protected $verband;

    /**
     * @var String
     */
    protected $bezeichnung;

    /**
     * @var String
     */
    protected $farbe;

    /**
     * @var Integer
     */
    protected $startalter;

    /**
     * @var Integer
     */
    protected $endalter;

    /**
     * @var String
     */
    protected $categorieId;

    /**
     * @return Int $uid
     */
    public function getUid() {
        return $this->uid;
    }
    /**
     * @param Int $uid
     */
    public function setUid($uid) {
        $this->uid = $uid;
    }

    /**
     * @return String
     */
    public function getVerband () {
        return $this->verband;
    }

    /**
     * @param String $verband
     */
    public function setVerband ($verband) {
        $this->verband = $verband;
    }

    /**
     * @return String
     */
    public function getBezeichnung () {
        return $this->bezeichnung;
    }

    /**
     * @param String $bezeichnung
     */
    public function setBezeichnung ($bezeichnung) {
        $this->bezeichnung = $bezeichnung;
    }

    /**
     * @return String
     */
    public function getFarbe () {
        return $this->farbe;
    }

    /**
     * @param String $farbe
     */
    public function setFarbe ($farbe) {
        $this->farbe = $farbe;
    }

    /**
     * @return int
     */
    public function getStartalter () {
        return $this->startalter;
    }

    /**
     * @param int $startalter
     */
    public function setStartalter ($startalter) {
        $this->startalter = $startalter;
    }

    /**
     * @return int
     */
    public function getEndalter () {
        return $this->endalter;
    }

    /**
     * @param int $endalter
     */
    public function setEndalter ($endalter) {
        $this->endalter = $endalter;
    }

    /**
     * @return String
     */
    public function getCategorieId () {
        return $this->categorieId;
    }

    /**
     * @param String $categorieId
     */
    public function setCategorieId ($categorieId) {
        $this->categorieId = $categorieId;
    }

    public function getImageURL(){
        return (string) "<img src='https://kalender.scoutnet.de/2.0/images/".$this->getUid().".gif' alt='".htmlentities($this->getBezeichnung(), ENT_COMPAT|ENT_HTML401, 'UTF-8')."' />";
    }

}
