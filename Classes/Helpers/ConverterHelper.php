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

use DateTime;
use ScoutNet\Api\Model\Category;
use ScoutNet\Api\Model\Event;
use ScoutNet\Api\Model\Index;
use ScoutNet\Api\Model\Permission;
use ScoutNet\Api\Model\Section;
use ScoutNet\Api\Model\Structure;
use ScoutNet\Api\Model\User;

class ConverterHelper
{
    /**
     * @var CacheHelper
     */
    private CacheHelper $cache;

    public function __construct($cache = null)
    {
        if ($cache === null) {
            $cache = new CacheHelper();
        }

        $this->cache = $cache;
    }

    public function convertEventToApi(Event $event): array
    {
        $array = [
            'ID' => $event->getUid() ?? -1,
            'UID' => $event->getUid() ?? -1,
            'SSID' => $event->getStructure()->getUid(),
            'Title' => $event->getTitle(),
            'Organizer' => $event->getOrganizer(),
            'Target_Group' => $event->getTargetGroup(),
            'Start' => $event->getStartTimestamp() instanceof DateTime ? DateTime::createFromFormat('d.m.Y H:i:s T', $event->getStartTimestamp()->format('d.m.Y H:i:s') . ' UTC')->format('U') : '',
            'End' => $event->getEndTimestamp() instanceof DateTime ? DateTime::createFromFormat('d.m.Y H:i:s T', $event->getEndTimestamp()->format('d.m.Y H:i:s') . ' UTC')->format('U') : '',
            'All_Day' => $event->getAllDayEvent(),
            'ZIP' => $event->getZip(),
            'Location' => $event->getLocation(),
            'URL_Text' => $event->getUrlText(),
            'URL' => $event->getUrl(),
            'Description' => $event->getDescription(),
            'Stufen' => [],
            'Keywords' => [],
            'Kalender' => $event->getStructure()->getUid(),
            'Last_Modified_By' => $event->getChangedBy()->getUid(),
            'Last_Modified_At' => $event->getChangedAt()->getTimestamp(),
            'Created_By' => $event->getCreatedBy()->getUid(),
            'Created_At' => $event->getCreatedAt()->getTimestamp(),
        ];

        foreach ($event->getStufen() as $stufe) {
            $array['Stufen'][] = $stufe->getUid();
        }

        $customKeywords = [];
        foreach ($event->getCategories() as $category) {
            if ($category->getUid() == null) {
                $customKeywords[] = $category->getText();
            } else {
                $array['Keywords'][$category->getUid()] = $category->getText();
            }
        }

        if (count($customKeywords) > 0) {
            $array['Custom_Keywords'] = $customKeywords;
        }

        return $array;
    }

    public function convertApiToEvent($array): Event
    {
        $event = new Event();

        $event->setUid($array['UID'] ?? -1);
        $event->setTitle($array['Title'] ?? '');
        $event->setOrganizer($array['Organizer'] ?? '');
        $event->setTargetGroup($array['Target_Group'] ?? '');

        // Time
        if (isset($array['Start'])) {
            $start = DateTime::createFromFormat('U', $array['Start']);
            $event->setStartDate(new DateTime($start->format('m/d/Y')));
            $event->setStartTime((isset($array['All_Day']) && $array['All_Day']) ? null : $start->format('H:i'));
        }
        if (isset($array['End'])) {
            $end = DateTime::createFromFormat('U', $array['End']);
            $event->setEndDate($array['End'] === 0 ? null : new DateTime($end->format('m/d/Y')));
            $event->setEndTime((isset($array['All_Day']) && $array['All_Day']) ? null : $end->format('H:i'));
        }

        // Location
        $event->setZip($array['ZIP'] ?? '');
        $event->setLocation($array['Location'] ?? '');

        // Links
        $event->setUrlText($array['URL_Text'] ?? '');
        $event->setUrl($array['URL'] ?? '');
        $event->setDescription($array['Description'] ?? '');

        if (isset($array['Last_Modified_At'])) {
            $event->setChangedAt($array['Last_Modified_At'] === 0 ? null : DateTime::createFromFormat('U', $array['Last_Modified_At']));
        }
        if (isset($array['Created_At'])) {
            $event->setCreatedAt($array['Created_At'] === 0 ? null : DateTime::createFromFormat('U', $array['Created_At']));
        }

        if (isset($array['Keywords'])) {
            foreach ($array['Keywords'] as $id => $text) {
                $category = $this->cache->get(Category::class, $id);
                if ($category === null) {
                    $category = $this->convertApiToCategory(['ID' => $id, 'Text' => $text]);
                }

                if ($category !== null) {
                    $event->addCategory($category);
                }
            }
        }

        // load event elements from cache
        if (isset($array['Last_Modified_By'])) {
            $last_modified_by = $this->cache->get(User::class, $array['Last_Modified_By']);
            if ($last_modified_by) {
                $event->setChangedBy($last_modified_by);
            }
        }
        if (isset($array['Created_By'])) {
            $created_by = $this->cache->get(User::class, $array['Created_By']);
            if ($created_by) {
                $event->setCreatedBy($created_by);
            }
        }

        if (isset($array['Kalender'])) {
            $structure = $this->cache->get(Structure::class, (int)($array['Kalender']));
            if ($structure) {
                $event->setStructure($structure);
            }
        }

        if (isset($array['Stufen'])) {
            foreach ($array['Stufen'] as $stufenCategoryId) {
                $stufe = $this->cache->get(Section::class, $stufenCategoryId);
                if ($stufe !== null) {
                    $event->addSection($stufe);
                }
            }
        }

        $this->cache->add($event);
        return $event;
    }

    public function convertApiToUser($array): User
    {
        $user = new User();

        $user->setUid($array['userid'] ?? -1);
        $user->setUsername($array['userid'] ?? '');
        $user->setFirstName($array['firstname'] ?? '');
        $user->setLastName($array['surname'] ?? '');
        $user->setSex($array['sex'] ?? '');

        $this->cache->add($user);
        return $user;
    }

    public function convertApiToSection($array): Section
    {
        $stufe = new Section();

        $stufe->setUid($array['id'] ?? -1);
        $stufe->setVerband($array['verband'] ?? '');
        $stufe->setBezeichnung($array['bezeichnung'] ?? '');
        $stufe->setFarbe($array['farbe'] ?? '');
        $stufe->setStartalter(isset($array['startalter']) ? (int)($array['startalter']) : -1);
        $stufe->setEndalter(isset($array['endalter']) ? (int)($array['endalter']) : -1);
        $stufe->setCategoryId($array['Keywords_ID'] ?? -1);

        $this->cache->add($stufe, $stufe->getCategoryId());
        return $stufe;
    }

    public function convertApiToStructure($array)
    {
        $structure = new Structure();
        $structure->setUid(isset($array['ID']) ? $array['ID'] : -1);
        $structure->setEbene(isset($array['Ebene']) ? $array['Ebene'] : '');
        $structure->setName(isset($array['Name']) ? $array['Name'] : '');
        $structure->setVerband(isset($array['Verband']) ? $array['Verband'] : '');
        $structure->setIdent(isset($array['Ident']) ? $array['Ident'] : '');
        $structure->setEbeneId(isset($array['Ebene_Id']) ? $array['Ebene_Id'] : 0);

        if (isset($array['Used_Kategories']) && is_array($array['Used_Kategories'])) {
            $used_categories = [];
            foreach ($array['Used_Kategories'] as $id => $text) {
                $used_categories[$id] = $this->convertApiToCategory(['ID' => $id, 'Text' => $text]);
            }

            $structure->setUsedCategories($used_categories);
        }
        if (isset($array['Forced_Kategories']) && is_array($array['Forced_Kategories'])) {
            $forced_categories = [];
            foreach ($array['Forced_Kategories'] as $name => $cat_array) {
                $forced_categories[$name] = [];
                if (! is_array($cat_array)) {
                    continue;
                }

                foreach ($cat_array as $id => $text) {
                    $forced_categories[$name][$id] = $this->convertApiToCategory(['ID' => $id, 'Text' => $text]);
                }
            }

            $structure->setForcedCategories($forced_categories);
        }

        $this->cache->add($structure);
        return $structure;
    }

    public function convertApiToPermission($array)
    {
        $permission = new Permission();

        $permission->setState(isset($array['code']) ? $array['code'] : Permission::AUTH_NO_RIGHT);
        $permission->setText(isset($array['text']) ? $array['text'] : '');
        $permission->setType(isset($array['type']) ? $array['type'] : '');

        return $permission;
    }

    public function convertApiToCategory($array): Category
    {
        $category = new Category();

        $category->setUid($array['ID'] ?? -1);
        $category->setText($array['Text'] ?? '');

        $this->cache->add($category);
        return $category;
    }

    public function convertApiToIndex($array): Index
    {
        $index = new Index();

        $index->setUid(isset($array['id']) ? $array['id'] : -1);
        $index->setNumber(isset($array['number']) ? $array['number'] : '');
        $index->setEbene(isset($array['ebene']) ? $array['ebene'] : '');
        $index->setName(isset($array['name']) ? $array['name'] : '');
        $index->setOrt(isset($array['ort']) ? $array['ort'] : '');
        $index->setPlz(isset($array['plz']) ? $array['plz'] : '');
        $index->setUrl(isset($array['url']) ? $array['url'] : '');
        $index->setLatitude(isset($array['latitude']) ? $array['latitude'] : 0.0);
        $index->setLongitude(isset($array['longitude']) ? $array['longitude'] : 0.0);
        $index->setParentId(isset($array['parent_id']) ? $array['parent_id'] : null);

        // this only works, because the api returns the children after the parents
        /**
         * @var Index $parent
         */
        if ($parent = $this->cache->get(Index::class, $index->getParentId())) {
            $parent->addChild($index);
        }

        $this->cache->add($index);
        return $index;
    }
}
