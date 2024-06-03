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
            'ID' => $event->getUid() !== null ? $event->getUid() : -1,
            'UID' => $event->getUid() !== null ? $event->getUid() : -1,
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

    public function convertApiToEvent($array)
    {
        $event = new Event();

        $event->setUid(isset($array['UID']) ? $array['UID'] : -1);
        $event->setTitle(isset($array['Title']) ? $array['Title'] : null);
        $event->setOrganizer(isset($array['Organizer']) ? $array['Organizer'] : null);
        $event->setTargetGroup(isset($array['Target_Group']) ? $array['Target_Group'] : null);

        // Time
        if (isset($array['Start'])) {
            $event->setStartDate(
                DateTime::createFromFormat('Y-m-d H:i:s', gmstrftime('%Y-%m-%d 00:00:00', $array['Start']))
            );
            $event->setStartTime((isset($array['All_Day']) && $array['All_Day']) ? null : gmstrftime('%H:%M:00', $array['Start']));
        }
        if (isset($array['End'])) {
            $event->setEndDate($array['End'] == 0 ? null : DateTime::createFromFormat('Y-m-d H:i:s', gmstrftime('%Y-%m-%d 00:00:00', $array['End'])));
            $event->setEndTime((isset($array['All_Day']) && $array['All_Day']) ? null : gmstrftime('%H:%M:00', $array['End']));
        }

        // Location
        $event->setZip(isset($array['ZIP']) ? $array['ZIP'] : null);
        $event->setLocation(isset($array['Location']) ? $array['Location'] : null);

        // Links
        $event->setUrlText(isset($array['URL_Text']) ? $array['URL_Text'] : null);
        $event->setUrl(isset($array['URL']) ? $array['URL'] : null);
        $event->setDescription(isset($array['Description']) ? $array['Description'] : null);

        if (isset($array['Last_Modified_At'])) {
            $event->setChangedAt($array['Last_Modified_At'] == 0 ? null : DateTime::createFromFormat('U', $array['Last_Modified_At']));
        }
        if (isset($array['Created_At'])) {
            $event->setCreatedAt($array['Created_At'] == 0 ? null : DateTime::createFromFormat('U', $array['Created_At']));
        }

        if (isset($array['Keywords'])) {
            foreach ($array['Keywords'] as $id => $text) {
                $category = $this->cache->get(Category::class, $id);
                if ($category === null) {
                    $category = $this->convertApiToCategorie(['ID' => $id, 'Text' => $text]);
                }

                if ($category !== null) {
                    $event->addCategory($category);
                }
            }
        }

        // load event elements from cache
        if (isset($array['Last_Modified_By'])) {
            $event->setChangedBy($this->cache->get(User::class, $array['Last_Modified_By']));
        }
        if (isset($array['Created_By'])) {
            $event->setCreatedBy($this->cache->get(User::class, $array['Created_By']));
        }

        if (isset($array['Kalender'])) {
            $event->setStructure($this->cache->get(Structure::class, (int)($array['Kalender'])));
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

    public function convertApiToUser($array)
    {
        $user = new User();

        $user->setUid(isset($array['userid']) ? $array['userid'] : -1);
        $user->setUsername(isset($array['userid']) ? $array['userid'] : null);
        $user->setFirstName(isset($array['firstname']) ? $array['firstname'] : null);
        $user->setLastName(isset($array['surname']) ? $array['surname'] : null);
        $user->setSex(isset($array['sex']) ? $array['sex'] : null);

        $this->cache->add($user);
        return $user;
    }

    public function convertApiToSection($array)
    {
        $stufe = new Section();

        $stufe->setUid(isset($array['id']) ? $array['id'] : -1);
        $stufe->setVerband(isset($array['verband']) ? $array['verband'] : null);
        $stufe->setBezeichnung(isset($array['bezeichnung']) ? $array['bezeichnung'] : '');
        $stufe->setFarbe(isset($array['farbe']) ? $array['farbe'] : '');
        $stufe->setStartalter(isset($array['startalter']) ? (int)($array['startalter']) : -1);
        $stufe->setEndalter(isset($array['endalter']) ? (int)($array['endalter']) : -1);
        $stufe->setCategoryId(isset($array['Keywords_ID']) ? $array['Keywords_ID'] : -1);

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
                $used_categories[$id] = $this->convertApiToCategorie(['ID' => $id, 'Text' => $text]);
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
                    $forced_categories[$name][$id] = $this->convertApiToCategorie(['ID' => $id, 'Text' => $text]);
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

    public function convertApiToCategorie($array)
    {
        $categorie = new Category();

        $categorie->setUid(isset($array['ID']) ? $array['ID'] : -1);
        $categorie->setText(isset($array['Text']) ? $array['Text'] : '');

        $this->cache->add($categorie);
        return $categorie;
    }

    public function convertApiToIndex($array)
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
