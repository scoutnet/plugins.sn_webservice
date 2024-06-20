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

use ScoutNet\Api\Model\AbstractModel;

class CacheHelper
{
    private array $cache = [];

    /**
     * @param AbstractModel $object
     * @param int|string|null $id
     *
     * @return AbstractModel|false
     */
    public function add(AbstractModel $object, int|string $id = null): AbstractModel|bool
    {
        $class = get_class($object);
        if ($id === null) {
            $id = $object->getUid();
        }

        // we can only insert object with id
        if ($id === null) {
            return false;
        }

        $this->cache[$class][$id] = $object;

        return $object;
    }

    /**
     * @param string $class
     * @param string|int $id
     *
     * @return AbstractModel|null
     */
    public function get(string $class, string|int $id): ?AbstractModel
    {
        return $this->cache[$class][$id] ?? null;
    }
}
