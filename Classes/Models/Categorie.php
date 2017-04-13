<?php

namespace ScoutNet\Api\Models;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 Stefan "Mütze" Horst <muetze@scoutnet.de>, ScoutNet
 *
 *  All rights reserved
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

class Categorie extends AbstractModel {
    private $array = [];

    public function __construct($array = []) {
        $this->array = $array;

        $this->uid = isset($array['ID'])?$array['ID']:-1;
        $this->text = isset($array['Text'])?$array['Text']:'';
        $this->available = false;
    }


    /**
     * @var string
     */
    protected $text;

    /**
     * @var bool
     */
    protected $available;

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