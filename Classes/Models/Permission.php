<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 11.04.17
 * Time: 09:40
 */

namespace ScoutNet\Api\Models;


class Permission {
    const AUTH_NO_RIGHT = 1;
    const AUTH_WRITE_ALLOWED = 0;
    const AUTH_REQUESTED = 0;
    const AUTH_REQUEST_PENDING = 2;

    var $state = self::AUTH_NO_RIGHT;
    var $text = '';
    var $type = '';

    function __construct($array) {
        $this->state = $array['code'];
        $this->text = $array['text'];
        $this->type = $array['type'];
    }

    public function getState() {
        return $this->state;
    }

    public function hasWritePermissionsPending() {
        return $this->state == SELF::AUTH_PENDING;
    }

    public function hasWritePermissions() {
        return $this->state == SELF::AUTH_WRITE_ALLOWED;
    }
}