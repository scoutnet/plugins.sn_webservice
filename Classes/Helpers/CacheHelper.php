<?php

namespace ScoutNet\Api\Helpers;

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