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

namespace ScoutNet\Api\Helpers;

use ScoutNet\Api\Models\AbstractModel;
use ScoutNet\Api\Models\Structure;
use ScoutNet\Api\Models\Stufe;
use ScoutNet\Api\Models\User;
use ScoutNet\Api\Models\Event;

class CacheHelper {
    private $cache = [];

    /**
     * @param AbstractModel $object
     * @param int          $id
     *
     * @return AbstractModel|false
     */
    public function add(AbstractModel &$object, $id=Null) {
        $class = get_class($object);
        if ($id == Null) {
            $id = $object->getUid();
        }

        // we can only insert object with id
        if ($id == null) {
            return false;
        }

        $this->cache[$class][$id] = $object;

        return $object;
    }

    /**
     * @param $class
     * @param $id
     *
     * @return mixed|null
     */
    public function get($class, $id) {
        if (isset($this->cache[$class]) && isset($this->cache[$class][$id])) {
            return $this->cache[$class][$id];
        }
        return null;
    }
}
