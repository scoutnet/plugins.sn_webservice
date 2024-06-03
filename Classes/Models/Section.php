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

namespace ScoutNet\Api\Models;

/**
 * Section
 */
class Section extends AbstractModel
{
    /**
     * @var string
     */
    protected string $verband;

    /**
     * @var string
     */
    protected string $bezeichnung;

    /**
     * @var string
     */
    protected string $farbe;

    /**
     * @var int
     */
    protected int $startalter = -1;

    /**
     * @var int
     */
    protected int $endalter = -1;

    /**
     * @var Category
     */
    protected Category $categoriy;

    /**
     * @return string
     */
    public function getVerband(): string
    {
        return $this->verband ?? '';
    }

    /**
     * @param string $verband
     */
    public function setVerband(string $verband): void
    {
        $this->verband = $verband;
    }

    /**
     * @return string
     */
    public function getBezeichnung(): string
    {
        return $this->bezeichnung ?? '';
    }

    /**
     * @param string $bezeichnung
     */
    public function setBezeichnung(string $bezeichnung): void
    {
        $this->bezeichnung = $bezeichnung;
    }

    /**
     * @return string
     */
    public function getFarbe(): string
    {
        return $this->farbe ?? '';
    }

    /**
     * @param string $farbe
     */
    public function setFarbe(string $farbe): void
    {
        $this->farbe = $farbe;
    }

    /**
     * @return int
     */
    public function getStartalter(): int
    {
        return $this->startalter ?? -1;
    }

    /**
     * @param int $startalter
     */
    public function setStartalter(int $startalter): void
    {
        $this->startalter = $startalter;
    }

    /**
     * @return int
     */
    public function getEndalter(): int
    {
        return $this->endalter ?? -1;
    }

    /**
     * @param int $endalter
     */
    public function setEndalter(int $endalter): void
    {
        $this->endalter = $endalter;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getCategorieId(): string
    {
        return $this->category?->getUid() ?? '';
    }

    /**
     * @param int $categoryId
     * @deprecated
     */
    public function setCategorieId(int $categoryId): void
    {
        //        $categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
        //        $this->category = $categoryRepository->findByUid($categoryId);
    }

    /**
     * @return string
     */
    public function getImageURL(): string
    {
        return (string)"<img src='https://kalender.scoutnet.de/2.0/images/" . $this->getUid() . ".gif' alt='" . htmlentities($this->getBezeichnung(), ENT_COMPAT | ENT_HTML401, 'UTF-8') . "' />";
    }
}
