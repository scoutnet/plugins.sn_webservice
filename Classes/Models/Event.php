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

class Event extends AbstractModel {
    private $array = [];

    function __construct($array = [], \ScoutNet\Api\Helpers\CacheHelper $cache = null) {
        // save array to class
        $this->array = $array;

        $this->uid = isset($array['UID']) ? $array['UID'] : -1;
        $this->title = isset($array['Title']) ? $array['Title'] : null;
        $this->organizer = isset($array['Organizer']) ? $array['Organizer'] : null;
        $this->targetGroup = isset($array['Target_Group']) ? $array['Target_Group'] : null;

        // Time
        if (isset($array['Start'])) {
            $this->startDate = \DateTime::createFromFormat('Y-m-d H:i:s', gmstrftime("%Y-%m-%d 00:00:00", $array['Start']));
            $this->startTime = (isset($array['All_Day']) && $array['All_Day']) ? null : gmstrftime('%H:%M:00', $array['Start']);
        }
        if (isset($array['End'])) {
            $this->endDate = $array['End'] == 0 ? null : \DateTime::createFromFormat('Y-m-d H:i:s', gmstrftime("%Y-%m-%d 00:00:00", $array['End']));
            $this->endTime = (isset($array['All_Day']) && $array['All_Day']) ? null : gmstrftime('%H:%M:00', $array['End']);
        }

        // Location
        $this->zip = isset($array['ZIP']) ? $array['ZIP'] : null;
        $this->location = isset($array['Location']) ? $array['Location'] : null;

        // Links
        $this->urlText = isset($array['URL_Text']) ? $array['URL_Text'] : null;
        $this->url = isset($array['URL']) ? $array['URL'] : null;
        $this->description = isset($array['Description']) ? $array['Description'] : null;

        if (isset($array['Last_Modified_At'])) {
            $this->changedAt = $array['Last_Modified_At'] == 0 ? null : \DateTime::createFromFormat('U', $array['Last_Modified_At']);
        }
        if (isset($array['Created_At'])) {
            $this->createdAt = $array['Created_At'] == 0 ? null : \DateTime::createFromFormat('U', $array['Created_At']);
        }

        if (isset($array['Keywords'])) {
            foreach ($array['Keywords'] as $id => $text) {
                $categorie = new Categorie(array('ID' => $id, 'Text' => $text));
                if ($categorie != null) {
                    $this->addCategorie($categorie);
                }
            }
        }

        if ($cache != null) {
            // load this elements from cache

            if (isset($array['Last_Modified_By'])) {
                $this->changedBy = $cache->get_user_by_id(intval($array['Last_Modified_By']));
            }
            if (isset($array['Created_By'])) {
                $this->createdBy = $cache->get_user_by_id(intval($array['Created_By']));
            }

            if (isset($array['Kalender'])) {
                $this->structure = $cache->get_kalender_by_id(intval($array['Kalender']));
            }


            if (isset($array['Stufen'])) {
                foreach ($array['Stufen'] as $stufenId) {
                    $stufe = $cache->get_stufe_by_id($stufenId);
                    if ($stufe != null) {
                        $this->addStufe($stufe);
                    }
                }
            }
        } else {
            // TODO: load this stuff from api
        }

    }

    public function __get($name) {
        return $this->{$name};
    }


    public function get_Author_name() {
        if (isset($this['Author']) && $this['Author'] != null) {
            return (string)htmlentities(utf8_decode($this['Author']->get_full_Name()));
        }

        return (string)"";
    }

    public function get_Stufen_Images() {
        if (isset($this['Stufen']) && $this['Stufen'] != null) {

            $stufen = "";
            foreach ($this['Stufen'] as $stufe) {
                $stufen .= $stufe->get_Image_URL();
            }

            return (string)$stufen;
        }
        return (string)"";
    }

    /**
     * @var string
     * @validate NotEmpty
     * @validate StringLength(minimum=2, maximum=80)
     */
    protected $title = '';

    /**
     * @var string
     * @validate StringLength(minimum=2, maximum=255)
     */
    protected $organizer = '';
    /**
     * @var string
     * @validate StringLength(minimum=2, maximum=255)
     */
    protected $targetGroup = '';

    /**
     * @var \DateTime
     * @validate NotEmpty
     */
    protected $startDate;
    /**
     * @var string
     */
    protected $startTime;

    /**
     * @var \DateTime
     */
    protected $endDate;
    /**
     * @var string
     */
    protected $endTime;

    /**
     * @var string
     * @validate StringLength(minimum=3, maximum=255)
     */
    protected $zip;

    /**
     * @var string
     * @validate StringLength(minimum=2, maximum=255)
     */
    protected $location;

    /**
     * @var string
     * @validate StringLength(minimum=3, maximum=255)
     */
    protected $urlText;

    /**
     * @var string
     * @validate StringLength(minimum=3, maximum=255)
     */
    protected $url;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \ScoutNet\ShScoutnetWebservice\Domain\Model\Stufe[]
     */
    protected $stufen = array();

    /**
     * @var \ScoutNet\ShScoutnetWebservice\Domain\Model\Categorie[]
     */
    protected $categories = array();

    /**
     * Structure
     *
     * @var \ScoutNet\Api\Models\Structure
     * validate NotEmpty
     * @lazy
     */
    protected $structure = NULL;

    /**
     * changedBy
     *
     * @var \ScoutNet\ShScoutnetWebservice\Domain\Model\User
     */
    protected $changedBy = NULL;

    /**
     * changedBy
     *
     * @var \ScoutNet\ShScoutnetWebservice\Domain\Model\User
     */
    protected $createdBy = NULL;

    /**
     * createdAt
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * changedAt
     *
     * @var \DateTime
     */
    protected $changedAt;

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getOrganizer() {
        return $this->organizer;
    }

    /**
     * @param string $organizer
     */
    public function setOrganizer($organizer) {
        $this->organizer = $organizer;
    }

    /**
     * @return string
     */
    public function getTargetGroup() {
        return $this->targetGroup;
    }

    /**
     * @param string $targetGroup
     */
    public function setTargetGroup($targetGroup) {
        $this->targetGroup = $targetGroup;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate() {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate) {
        $this->startDate = $startDate;
    }

    /**
     * @return string
     */
    public function getStartTime() {
        return $this->startTime;
    }

    /**
     * @param string $startTime
     */
    public function setStartTime($startTime) {
        $this->startTime = $startTime;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate() {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate) {
        $this->endDate = $endDate;
    }

    /**
     * @return string
     */
    public function getEndTime() {
        return $this->endTime;
    }

    /**
     * @param string $endTime
     */
    public function setEndTime($endTime) {
        $this->endTime = $endTime;
    }

    /**
     * @return string
     */
    public function getZip() {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip($zip) {
        $this->zip = $zip;
    }

    /**
     * @return string
     */
    public function getLocation() {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation($location) {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getUrlText() {
        return $this->urlText;
    }

    /**
     * @param string $urlText
     */
    public function setUrlText($urlText) {
        $this->urlText = $urlText;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @return \ScoutNet\Api\Models\Structure
     */
    public function getStructure() {
        return $this->structure;
    }

    /**
     * @param \ScoutNet\Api\Models\Structure $structure
     */
    public function setStructure($structure) {
        $this->structure = $structure;
    }

    /**
     * @return \ScoutNet\ShScoutnetWebservice\Domain\Model\User
     */
    public function getChangedBy() {
        return $this->changedBy;
    }

    /**
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\User $changedBy
     */
    public function setChangedBy($changedBy) {
        $this->changedBy = $changedBy;
    }

    /**
     * @return \ScoutNet\ShScoutnetWebservice\Domain\Model\User
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * @param \ScoutNet\ShScoutnetWebservice\Domain\Model\User $createdBy
     */
    public function setCreatedBy($createdBy) {
        $this->createdBy = $createdBy;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getChangedAt() {
        return $this->changedAt;
    }

    /**
     * @param \DateTime $changedAt
     */
    public function setChangedAt($changedAt) {
        $this->changedAt = $changedAt;
    }

    /**
     * @return array
     */
    public function getCategories() {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories($categories) {
        $this->categories = $categories;
    }

    /**
     * @param \ScoutNet\Api\Models\Categorie $categorie
     */
    public function addCategorie(Categorie $categorie) {
        $this->categories[$categorie->getUid()] = $categorie;
    }


    public function getAuthor() {
        if ($this->changedBy != null) return $this->changedBy;
        return $this->createdBy;
    }

    /**
     * @return string
     */
    public function getStufenImages() {
        if (isset($this->stufen) && $this->stufen != null) {

            $stufen = "";
            /** @var \ScoutNet\ShScoutnetWebservice\Domain\Model\Stufe $stufe */
            foreach ($this->stufen as $stufe) {
                $stufen .= $stufe->getImageURL();
            }

            return (string)$stufen;
        }
        return (string)"";
    }

    public function getStufenCategories() {
        $categories = array();
        foreach ($this->stufen as $stufe) {
            $cat = new Categorie();
            $cat->setUid($stufe->getCategorieId());
            $cat->setText($stufe->getBezeichnung());

            $categories[$cat->getUid()] = $cat;
        }

        return $categories;
    }


    public function getStartTimestamp() {
        if ($this->startTime) {
            $startTimestamp = \DateTime::createFromFormat('Y-m-d H:i:s', $this->startDate->format('Y-m-d') . ' ' . $this->startTime . (substr_count($this->startTime, ':') == 1 ? ':00' : ''));
        } else {
            $startTimestamp = \DateTime::createFromFormat('Y-m-d H:i:s', $this->startDate->format('Y-m-d') . ' 00:00:00');
        }

        return $startTimestamp;
    }

    public function getEndTimestamp() {
        if ($this->endDate && $this->endTime) {
            $endTimestamp = \DateTime::createFromFormat('Y-m-d H:i:s', $this->endDate->format('Y-m-d') . ' ' . $this->endTime . (substr_count($this->endTime, ':') == 1 ? ':00' : ''));
        } elseif ($this->endTime) {
            $endTimestamp = \DateTime::createFromFormat('Y-m-d H:i:s', $this->startDate->format('Y-m-d') . ' ' . $this->endTime . (substr_count($this->endTime, ':') == 1 ? ':00' : ''));
        } elseif ($this->endDate) {
            $endTimestamp = \DateTime::createFromFormat('Y-m-d H:i:s', $this->endDate->format('Y-m-d') . ' 00:00:00');
        } else {
            $endTimestamp = $this->getStartTimestamp();
        }
        return $endTimestamp;
    }

    public function getShowEndDateOrTime() {
        return $this->getShowEndDate() || $this->getShowEndTime();
    }

    public function getShowEndDate() {
        return !is_null($this->endDate) && $this->endDate != 0 && $this->endDate != $this->startDate;
    }

    public function getShowEndTime() {
        return !is_null($this->endTime);
    }

    public function getAllDayEvent() {
        return is_null($this->startTime);
    }

    public function getStartYear() {
        return $this->startDate->format('Y');
    }

    public function getStartMonth() {
        return $this->startDate->format('m');
    }

    /**
     * @return mixed
     */
    public function getStufen() {
        return $this->stufen;
    }

    /**
     * @param mixed $stufen
     */
    public function setStufen($stufen) {
        $this->stufen = $stufen;
    }

    /**
     * @param $stufe
     */
    public function addStufe($stufe) {
        $this->stufen[] = $stufe;
    }

    public function getShowDetails() {
        return trim($this->getDescription() . $this->getZip() . $this->getLocation() . $this->getOrganizer() . $this->getTargetGroup() . $this->getUrl()) !== '';
    }


    public function copyProperties($event) {
        $copyProperties = array('title', 'organizer', 'targetGroup', 'startDate', 'startTime', 'endDate', 'endTime', 'zip', 'location', 'urlText', 'url', 'description', 'structure', 'categories');

        foreach ($copyProperties as $propertie) {
            $this->{$propertie} = $event->{$propertie};
        }
    }
}
