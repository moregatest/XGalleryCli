<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="jav_movies")
 * @ORM\Entity(repositoryClass="App\Repository\JavMovieRepository")
 */
class JavMovie
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $sales_date;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $release_date;

    /**
     * @ORM\Column(type="string", length=125)
     */
    private $item_number;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $time;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return JavMovie|null
     */
    public function setItem(?int $id): ?self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return JavMovie
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getSalesDate(): ?DateTimeInterface
    {
        return $this->sales_date;
    }

    /**
     * @param DateTimeInterface $sales_date
     * @return JavMovie
     */
    public function setSalesDate(DateTimeInterface $sales_date): self
    {
        $this->sales_date = $sales_date;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getReleaseDate(): ?DateTimeInterface
    {
        return $this->release_date;
    }

    /**
     * @param DateTimeInterface|null $release_date
     * @return JavMovie
     */
    public function setReleaseDate(?DateTimeInterface $release_date): self
    {
        $this->release_date = $release_date;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getItemNumber(): ?string
    {
        return $this->item_number;
    }

    /**
     * @param string $item_number
     * @return JavMovie
     */
    public function setItemNumber(string $item_number): self
    {
        $this->item_number = $item_number;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return JavMovie
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTime(): ?int
    {
        return $this->time;
    }

    /**
     * @param int|null $time
     * @return JavMovie
     */
    public function setTime(?int $time): self
    {
        $this->time = $time;

        return $this;
    }
}
