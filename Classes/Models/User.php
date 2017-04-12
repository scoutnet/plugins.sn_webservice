<?php
namespace ScoutNet\Api\Models;

class User {
    private $array = [];

    function __construct($array) {
        $this->array = $array;

        $this->uid = $array['userid'];
        $this->username = $array['userid'];
        $this->firstName = $array['firstname'];
        $this->lastName = $array['surname'];
        $this->sex = $array['sex'];
    }

    public function __get($name) {
        return $this->{$name};
    }

    /**
     * @var int
     */
    protected $uid = null;

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
