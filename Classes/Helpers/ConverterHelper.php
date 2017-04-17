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

use ScoutNet\Api\Models\Categorie;
use ScoutNet\Api\Models\Event;
use ScoutNet\Api\Models\Permission;
use ScoutNet\Api\Models\Structure;
use ScoutNet\Api\Models\Stufe;
use ScoutNet\Api\Models\User;

class ConverterHelper {
    /**
     * @var \ScoutNet\Api\Helpers\CacheHelper
     */
    private $cache = null;

    public function __construct($cache = null) {
        if ($cache == null) {
            $cache = new CacheHelper();
        }

        $this->cache = $cache;
    }


    public function convertEventToApi($event) {

    }

    public function convertApiToEvent($array) {
        $event = new Event();

        $event->setUid(isset($array['UID']) ? $array['UID'] : -1);
        $event->setTitle(isset($array['Title']) ? $array['Title'] : null);
        $event->setOrganizer(isset($array['Organizer']) ? $array['Organizer'] : null);
        $event->setTargetGroup(isset($array['Target_Group']) ? $array['Target_Group'] : null);

        // Time
        if (isset($array['Start'])) {
            $event->setStartDate(\DateTime::createFromFormat('Y-m-d H:i:s', gmstrftime("%Y-%m-%d 00:00:00", $array['Start'])));
            $event->setStartTime((isset($array['All_Day']) && $array['All_Day']) ? null : gmstrftime('%H:%M:00', $array['Start']));
        }
        if (isset($array['End'])) {
            $event->setEndDate($array['End'] == 0 ? null : \DateTime::createFromFormat('Y-m-d H:i:s', gmstrftime("%Y-%m-%d 00:00:00", $array['End'])));
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
            $event->setChangedAt($array['Last_Modified_At'] == 0 ? null : \DateTime::createFromFormat('U', $array['Last_Modified_At']));
        }
        if (isset($array['Created_At'])) {
            $event->setCreatedAt($array['Created_At'] == 0 ? null : \DateTime::createFromFormat('U', $array['Created_At']));
        }

        if (isset($array['Keywords'])) {
            foreach ($array['Keywords'] as $id => $text) {
                $categorie = $this->cache->get(Categorie::class, $id);
                if ($categorie == null) {
                    $categorie = $this->convertApiToCategorie(array('ID' => $id, 'Text' => $text));
                }

                if ($categorie != null) {
                    $event->addCategorie($categorie);
                }
            }
        }


        // load event elements from cache

        if (isset($array['Last_Modified_By'])) {
            $event->setChangedBy($this->cache->get(User::class, intval($array['Last_Modified_By'])));
        }
        if (isset($array['Created_By'])) {
            $event->setCreatedBy($this->cache->get(User::class, intval($array['Created_By'])));
        }

        if (isset($array['Kalender'])) {
            $event->setStructure($this->cache->get(Structure::class, intval($array['Kalender'])));
        }


        if (isset($array['Stufen'])) {
            foreach ($array['Stufen'] as $stufenId) {
                $stufe = $this->cache->get_stufe_by_id($stufenId);
                if ($stufe != null) {
                    $event->addStufe($stufe);
                }
            }
        }

        $this->cache->add($event);
        return $event;
    }


    public function convertApiToUser($array) {
        $user = new User();

        $user->setUid(isset($array['userid']) ? $array['userid'] : -1);
        $user->setUsername(isset($array['userid']) ? $array['userid'] : null);
        $user->setFirstName(isset($array['firstname']) ? $array['firstname'] : null);
        $user->setLastName(isset($array['surname']) ? $array['surname'] : null);
        $user->setSex(isset($array['sex']) ? $array['sex'] : null);

        $this->cache->add($user);
        return $user;
    }

    public function convertApiToStufe($array) {
        $stufe = new Stufe();

        $stufe->setUid(isset($array['id']) ? $array['id'] : -1);
        $stufe->setVerband(isset($array['verband']) ? $array['verband'] : null);
        $stufe->setBezeichnung(isset($array['bezeichnung']) ? $array['bezeichnung'] : '');
        $stufe->setFarbe(isset($array['farbe']) ? $array['farbe'] : '');
        $stufe->setStartalter(isset($array['startalter']) ? intval($array['startalter']) : -1);
        $stufe->setEndalter(isset($array['endalter']) ? intval($array['endalter']) : -1);
        $stufe->setCategorieId(isset($array['Keywords_ID']) ? $array['Keywords_ID'] : -1);

        $this->cache->add($stufe);
        return $stufe;
    }

    public function convertApiToStructure($array) {
        $structure = new Structure();
        $structure->setUid(isset($array['ID']) ? $array['ID'] : -1);
        $structure->setEbene(isset($array['Ebene']) ? $array['Ebene'] : '');
        $structure->setName(isset($array['Name']) ? $array['Name'] : '');
        $structure->setVerband(isset($array['Verband']) ? $array['Verband'] : '');
        $structure->setIdent(isset($array['Ident']) ? $array['Ident'] : '');
        $structure->setEbeneId(isset($array['Ebene_Id']) ? $array['Ebene_Id'] : 0);

        $structure->setUsedCategories(isset($array['Used_Kategories']) ? $array['Used_Kategories'] : []);
        $structure->setForcedCategories(isset($array['Forced_Kategories']) ? $array['Forced_Kategories'] : []);

        $this->cache->add($structure);
        return $structure;
    }

    public function convertApiToPermission($array) {
        $permission = new Permission();

        $permission->setState(isset($array['code'])?$array['code']:Permission::AUTH_NO_RIGHT);
        $permission->setText(isset($array['text'])?$array['text']:'');
        $permission->setType(isset($array['type'])?$array['type']:'');

        return $permission;
    }

    public function convertApiToCategorie($array) {
        $categorie = new Categorie();

        $categorie->setUid(isset($array['ID'])?$array['ID']:-1);
        $categorie->setText(isset($array['Text'])?$array['Text']:'');
        $categorie->setAvailable(false);


        $this->cache->add($categorie);
        return $categorie;
    }
}