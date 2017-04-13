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

class User extends AbstractModel {
    const SEX_MALE = 'm';
    const SEX_FEMALE = 'w';

    private $array = [];

    function __construct($array = []) {
        $this->array = $array;

        $this->uid = isset($array['userid'])?$array['userid']:-1;
        $this->username = isset($array['userid'])?$array['userid']:null;
        $this->firstName = isset($array['firstname'])?$array['firstname']:null;
        $this->lastName = isset($array['surname'])?$array['surname']:null;
        $this->sex = isset($array['sex'])?$array['sex']:null;
    }


    /**
     * @var string
     * @validate NotEmpty
     * @validate StringLength(minimum=2, maximum=80)
     */
    protected $username = null;

    /**
     * @var string
     * @validate NotEmpty
     * @validate StringLength(minimum=2, maximum=80)
     */
    protected $firstName = null;

    /**
     * @var string
     * @validate NotEmpty
     * @validate StringLength(minimum=2, maximum=80)
     */
    protected $lastName = null;

    /**
     * @var string
     * @validate NotEmpty
     * @validate StringLength(minimum=1, maximum=80)
     */
    protected $sex = null;

    /**
     * @return string
     */
    public function getUsername () {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername ($username) {
        $this->username = $username;
    }

    public function getFirstName(){
        return trim($this->firstName)?trim($this->firstName):$this->getUsername();
    }

    /**
     * @param string $firstName
     */
    public function setFirstName ($firstName) {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName () {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName ($lastName) {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getSex () {
        return $this->sex;
    }

    /**
     * @param string $sex
     */
    public function setSex ($sex) {
        $this->sex = $sex;
    }

    public function getFullName(){
        // we use the class functions not the getters, because the firstname can be the username
        $full_name = $this->firstName.' '.$this->lastName;
        return trim($full_name) ? trim($full_name) : $this->getUsername();
    }
    public function getLongName(){
        $full_name = $this->getFullName();
        if( $full_name ){
            return $full_name.' ('.$this->getUsername().')';
        } else {
            return $this->getUsername();
        }
    }
}
