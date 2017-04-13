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

class Permission {
    const AUTH_NO_RIGHT = 1;
    const AUTH_WRITE_ALLOWED = 0;
    const AUTH_REQUESTED = 0;
    const AUTH_REQUEST_PENDING = 2;

    protected $state = self::AUTH_NO_RIGHT;
    protected $text = '';
    protected $type = '';

    function __construct($array = []) {
        $this->state = isset($array['code'])?$array['code']:self::AUTH_NO_RIGHT;
        $this->text = isset($array['text'])?$array['text']:'';
        $this->type = isset($array['type'])?$array['type']:'';
    }

    public function getState() {
        return $this->state;
    }

    public function hasWritePermissionsPending() {
        return $this->state == self::AUTH_REQUEST_PENDING;
    }

    public function hasWritePermissions() {
        return $this->state == self::AUTH_WRITE_ALLOWED;
    }

    /**
     * @return mixed|string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * @param mixed|string $text
     */
    public function setText($text) {
        $this->text = $text;
    }

    /**
     * @return mixed|string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param mixed|string $type
     */
    public function setType($type) {
        $this->type = $type;
    }
}