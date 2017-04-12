<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 12.04.17
 * Time: 10:38
 */

namespace ScoutNet\Api\Helpers;


use ScoutNet\Api\Models\Structure;
use ScoutNet\Api\Models\Stufe;
use ScoutNet\Api\Models\User;
use ScoutNet\Api\Models\Event;

class CacheHelper {
    private $cache = [];

    public function add($object) {
        $class = get_class($object);
        $id = $object->getUid();

        $this->cache[$class][$id] = $object;
    }

    public function get($class, $id) {
        if (isset($this->cache[$class]) && isset($this->cache[$class][$id])) {
            return $this->cache[$class][$id];
        }
        return null;
    }

    public function get_event_by_id($id) {
        return $this->get(Event::class, $id);
    }

    public function get_stufe_by_id($id) {
        return $this->get(Stufe::class, $id);
    }

    public function get_kalender_by_id($id) {
        return $this->get(Structure::class, $id);
    }

    public function get_user_by_id($id) {
        return $this->get(User::class, $id);
    }

}