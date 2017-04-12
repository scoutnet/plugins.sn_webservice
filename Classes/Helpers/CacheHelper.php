<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 12.04.17
 * Time: 10:38
 */

namespace ScoutNet\Api\Helpers;


class CacheHelper {
    private $cache = [];


    public function add($type, $id, $object) {
        $this->cache[$type][$id] = $object;
    }

    public function get_event_by_id($id) {
        return $this->get('event', $id);
    }

    public function get_stufe_by_id($id) {
        return $this->get('stufe', $id);
    }

    public function get_kalender_by_id($id) {
        return $this->get('structure', $id);
    }

    public function get_user_by_id($id) {
        return $this->get('user', $id);
    }

    public function get($type, $id) {
        if (isset($this->cache[$type]) && isset($this->cache[$type][$id])) {
            return $this->cache[$type][$id];
        }
        return null;
    }

}