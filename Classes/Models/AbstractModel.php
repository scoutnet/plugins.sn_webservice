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

abstract class AbstractModel {
    /**
     * @param $name
     *
     * @return mixed
     * @deprecated
     */
    public function __get($name) {
        return $this->{$name};
    }

    /**
     * @var int
     */
    protected $uid = null;

    /**
     * @return int
     */
    public function getUid(){
        return $this->uid;
    }

    /**
     * @param $uid int
     */
    public function setUid($uid){
        $this->uid = $uid;
    }

}