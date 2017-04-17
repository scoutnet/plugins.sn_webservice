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

class Structure extends AbstractModel {
    /**
     * @var String
     */
    protected $ebene;
    /**
     * @var String
     */
    protected $name;
    /**
     * @var String
     */
    protected $verband;
    /**
     * @var String
     */
    protected $ident;
    /**
     * @var Integer
     */
    protected $ebeneId;
    /**
     * @var array
     */
    protected $usedCategories = [];
    /**
     * @var array
     */
    protected $forcedCategories = [];

    /**
     * @return String
     */
    public function getEbene () {
        return $this->ebene;
    }

    /**
     * @param String $ebene
     */
    public function setEbene ($ebene) {
        $this->ebene = $ebene;
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
    public function getIdent () {
        return $this->ident;
    }

    /**
     * @param String $ident
     */
    public function setIdent ($ident) {
        $this->ident = $ident;
    }

    /**
     * @return int
     */
    public function getEbeneId () {
        return $this->ebeneId;
    }

    /**
     * @param int $ebeneId
     */
    public function setEbeneId ($ebeneId) {
        $this->ebeneId = $ebeneId;
    }

    /**
     * @return array
     */
    public function getUsedCategories () {
        return $this->usedCategories;
    }

    /**
     * @param array $usedCategories
     */
    public function setUsedCategories ($usedCategories) {
        $this->usedCategories = $usedCategories;
    }

    /**
     * @return array
     */
    public function getForcedCategories () {
        return $this->forcedCategories;
    }

    /**
     * @param array $forcedCategories
     */
    public function setForcedCategories ($forcedCategories) {
        $this->forcedCategories = $forcedCategories;
    }

    /**
     * @return String
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param String $name
     */
    public function setName($name) {
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getLongName() {
        return (string) $this->getEbene().(($this->getEbeneId() >= 7)?" ".$this->getName():"");
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
    public function get_Name() {
        return (string) $this->getEbene().(($this->getEbeneId() >= 7)?'&nbsp;'.$this->getName():"");
    }
}
