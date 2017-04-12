<?php

namespace ScoutNet\Api\Models;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Stefan "Mütze" Horst <muetze@scoutnet.de>
 *  All rights reserved
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class Categorie {
    private $array = [];

    public function __construct($array) {
        $this->array = $array;

        $this->uid = $array['ID'];
        $this->text = $array['Text'];
        $this->available = true;
    }


    /**
     * @var integer
     */
    protected $uid;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var bool
     */
    protected $available;

    /**
     * @return int
     */
    public function getUid() {
        return $this->uid;
    }

    /**
     * @param int $uid
     */
    public function setUid($uid) {
        $this->uid = $uid;
    }

    /**
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text) {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getAvailable() {
        return $this->available;
    }

    /**
     * @param mixed $available
     */
    public function setAvailable($available) {
        $this->available = $available;
    }
}