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

namespace ScoutNet\Api\Model;

use DateTime;

class Event extends AbstractModel
{
    /**
     * @var string
     * @validate NotEmpty
     * @validate StringLength(minimum=2, maximum=80)
     */
    protected string $title = 'new Event';

    /**
     * @var string
     * @validate StringLength(minimum=2, maximum=255)
     */
    protected string $organizer = '';

    /**
     * @var string
     * @validate StringLength(minimum=2, maximum=255)
     */
    protected string $targetGroup = '';

    /**
     * @var DateTime
     * @validate NotEmpty
     */
    protected DateTime $startDate;
    /**
     * @var string|null
     */
    protected ?string $startTime = null;

    /**
     * @var DateTime|null
     */
    protected ?DateTime $endDate = null;
    /**
     * @var string|null
     */
    protected ?string $endTime = null;

    /**
     * @var string
     * @validate StringLength(minimum=3, maximum=255)
     */
    protected string $zip = '';

    /**
     * @var string
     * @validate StringLength(minimum=2, maximum=255)
     */
    protected string $location = '';

    /**
     * @var string
     * @validate StringLength(minimum=3, maximum=255)
     */
    protected string $urlText = '';

    /**
     * @var string
     * @validate StringLength(minimum=3, maximum=255)
     */
    protected string $url = '';

    /**
     * @var string
     */
    protected string $description = '';

    /**
     * @var Section[]
     * // TODO: check if we need this, since this is part of a category
     */
    protected array $sections = [];

    /**
     * @var Category[]
     */
    protected array $categories = [];

    /**
     * Structure
     *
     * @var Structure
     * validate NotEmpty
     * @lazy
     */
    protected Structure $structure;

    /**
     * changedBy
     *
     * @var User|null
     */
    protected ?User $changedBy = null;

    /**
     * changedBy
     *
     * @var User|null
     */
    protected ?User $createdBy = null;

    /**
     * createdAt
     *
     * @var DateTime
     */
    protected DateTime $createdAt;

    /**
     * changedAt
     *
     * @var DateTime|null
     */
    protected ?DateTime $changedAt = null;

    /**
     * @param string $title
     * @param DateTime|null $startDate
     */
    public function __construct(string $title = 'new Event', DateTime $startDate = null)
    {
        $this->title = $title;
        $this->startDate = $startDate ?? new DateTime('now');

        $this->createdAt = $this->createdAt ?? new DateTime('now');
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getOrganizer(): string
    {
        return $this->organizer;
    }

    /**
     * @param string $organizer
     */
    public function setOrganizer(string $organizer): void
    {
        $this->organizer = $organizer;
    }

    /**
     * @return string
     */
    public function getTargetGroup(): string
    {
        return $this->targetGroup;
    }

    /**
     * @param string $targetGroup
     */
    public function setTargetGroup(string $targetGroup): void
    {
        $this->targetGroup = $targetGroup;
    }

    /**
     * @return DateTime
     */
    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime $startDate
     */
    public function setStartDate(DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return string|null
     */
    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    /**
     * @param string|null $startTime
     */
    public function setStartTime(?string $startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * @return DateTime|null
     */
    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    /**
     * @param DateTime|null $endDate
     */
    public function setEndDate(?DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return string|null
     */
    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    /**
     * @param string|null $endTime
     */
    public function setEndTime(?string $endTime): void
    {
        $this->endTime = $endTime;
    }

    /**
     * @return string
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip(string $zip): void
    {
        $this->zip = $zip;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getUrlText(): string
    {
        return $this->urlText;
    }

    /**
     * @param string $urlText
     */
    public function setUrlText(string $urlText): void
    {
        $this->urlText = $urlText;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return Structure
     */
    public function getStructure(): Structure
    {
        return $this->structure;
    }

    /**
     * @param Structure $structure
     */
    public function setStructure(Structure $structure): void
    {
        $this->structure = $structure;
    }

    /**
     * @return User|null
     */
    public function getChangedBy(): ?User
    {
        return $this->changedBy;
    }

    /**
     * @param User|null $changedBy
     */
    public function setChangedBy(?User $changedBy): void
    {
        $this->changedBy = $changedBy;
    }

    /**
     * @return User|null
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * @param User|null $createdBy
     */
    public function setCreatedBy(?User $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime|null $createdAt
     */
    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTime|null
     */
    public function getChangedAt(): ?DateTime
    {
        return $this->changedAt;
    }

    /**
     * @param DateTime|null $changedAt
     */
    public function setChangedAt(?DateTime $changedAt): void
    {
        $this->changedAt = $changedAt;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @param Category $category
     */
    public function addCategory(Category $category): void
    {
        $this->categories[$category->getUid()] = $category;
    }

    /**
     * @return User|null
     * @deprecated
     */
    public function getAuthor(): ?User
    {
        return $this->changedBy ?? $this->createdBy;
    }

    /**
     * @return Section[]
     * @deprecated
     */
    public function getStufen(): array
    {
        return $this->getSections();
    }

    /**
     * @param Section[] $sections
     * @deprecated
     */
    public function setStufen(array $sections): void
    {
        $this->setSections($sections);
    }

    /**
     * @param Section $stufe
     * @deprecated
     */
    public function addStufe(Section $stufe): void
    {
        $this->addSection($stufe);
    }

    /**
     * @return Section[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * @param Section[] $sections
     */
    public function setSections(array $sections): void
    {
        $this->sections = $sections;
    }

    /**
     * @param Section $section
     */
    public function addSection(Section $section): void
    {
        $this->sections[] = $section;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getStufenImages(): string
    {
        return $this->getSectionImages();
    }

    /**
     * @return string
     */
    public function getSectionImages(): string
    {
        $sections = '';
        foreach ($this->getSections() as $section) {
            $sections .= $section->getImageURL();
        }
        return $sections;
    }

    /**
     * @return array
     * @deprecated
     */
    public function getStufenCategories(): array
    {
        return $this->getSectionCategories();
    }

    /**
     * @return array
     */
    public function getSectionCategories(): array
    {
        $categories = [];
        foreach ($this->getSections() as $section) {
            $cat = $section->getCategory();
            $categories[$cat->getUid()] = $cat;
        }

        return $categories;
    }

    /**
     * @return DateTime
     */
    public function getStartTimestamp(): DateTime
    {
        if ($this->startTime) {
            $startTimestamp = DateTime::createFromFormat('Y-m-d H:i:s', $this->startDate->format('Y-m-d') . ' ' . $this->startTime . ((substr_count($this->startTime, ':') === 1) ? ':00' : ''));
        } else {
            $startTimestamp = DateTime::createFromFormat('Y-m-d H:i:s', $this->startDate->format('Y-m-d') . ' 00:00:00');
        }

        return $startTimestamp;
    }

    /**
     * @return DateTime
     */
    public function getEndTimestamp(): DateTime
    {
        if ($this->endDate && $this->endTime) {
            $endTimestamp = DateTime::createFromFormat('Y-m-d H:i:s', $this->endDate->format('Y-m-d') . ' ' . $this->endTime . (substr_count($this->endTime, ':') === 1 ? ':00' : ''));
        } elseif ($this->endTime) {
            $endTimestamp = DateTime::createFromFormat('Y-m-d H:i:s', $this->startDate->format('Y-m-d') . ' ' . $this->endTime . (substr_count($this->endTime, ':') === 1 ? ':00' : ''));
        } elseif ($this->endDate) {
            $endTimestamp = DateTime::createFromFormat('Y-m-d H:i:s', $this->endDate->format('Y-m-d') . ' 00:00:00');
        } else {
            $endTimestamp = $this->getStartTimestamp();
        }
        return $endTimestamp;
    }

    /**
     * @return bool
     */
    public function getShowEndDateOrTime(): bool
    {
        return $this->getShowEndDate() || $this->getShowEndTime();
    }

    /**
     * @return bool
     */
    public function getShowEndDate(): bool
    {
        return !is_null($this->endDate) && $this->endDate != 0 && $this->endDate != $this->startDate;
    }

    /**
     * @return bool
     */
    public function getShowEndTime(): bool
    {
        return !is_null($this->endTime);
    }

    /**
     * @return bool
     */
    public function getAllDayEvent(): bool
    {
        return is_null($this->startTime);
    }

    /**
     * @return string
     */
    public function getStartYear(): string
    {
        return $this->startDate->format('Y');
    }

    /**
     * @return string
     */
    public function getStartMonth(): string
    {
        return $this->startDate->format('m');
    }

    /**
     * @return bool
     */
    public function getShowDetails(): bool
    {
        return trim($this->getDescription() . $this->getZip() . $this->getLocation() . $this->getOrganizer() . $this->getTargetGroup() . $this->getUrl()) !== '';
    }

    /**
     * @param Event $event
     */
    public function copyProperties(Event $event): void
    {
        $copyProperties = [ 'title', 'organizer', 'targetGroup', 'startDate', 'startTime', 'endDate', 'endTime', 'zip', 'location', 'urlText', 'url', 'description', 'structure', 'sections', 'categories'];

        foreach ($copyProperties as $property) {
            $this->{$property} = $event->{$property};
        }
    }
}
