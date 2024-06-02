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
