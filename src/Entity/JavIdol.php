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

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * JavIdol
 *
 * @ORM\Table(name="jav_idols")
 * @ORM\Entity(repositoryClass="App\Repository\JavIdolRepository")
 */
class JavIdol
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=125, nullable=false)
     */
    private $source;

    /**
     * @var int
     *
     * @ORM\Column(name="xid", type="integer", nullable=false)
     */
    private $xid;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    private $birthday;

    /**
     * @var string|null
     *
     * @ORM\Column(name="blood_type", type="string", length=10, nullable=true)
     */
    private $bloodType;

    /**
     * @var int|null
     *
     * @ORM\Column(name="city", type="string", nullable=true)
     */
    private $city;

    /**
     * @var int|null
     *
     * @ORM\Column(name="height", type="smallint", nullable=true)
     */
    private $height;

    /**
     * @var int|null
     *
     * @ORM\Column(name="favorite", type="integer", nullable=true)
     */
    private $favorite;

    /**
     * @var int|null
     *
     * @ORM\Column(name="breast", type="smallint", nullable=true)
     */
    private $breast;

    /**
     * @var int|null
     *
     * @ORM\Column(name="waist", type="smallint", nullable=true)
     */
    private $waist;

    /**
     * @var int|null
     *
     * @ORM\Column(name="hips", type="integer", nullable=true)
     */
    private $hips;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @param string|null $source
     * @return JavIdol
     */
    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getXId(): ?int
    {
        return $this->xid;
    }

    /**
     * @param int|null $xid
     * @return JavIdol
     */
    public function setXId(?int $xid): self
    {
        $this->xid = $xid;

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
     * @param string|null $name
     * @return JavIdol
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getBirthday(): ?DateTimeInterface
    {
        return $this->birthday;
    }

    /**
     * @param DateTimeInterface|null $birthday
     * @return JavIdol
     */
    public function setBirthday(?DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBloodType(): ?string
    {
        return $this->bloodType;
    }

    /**
     * @param string|null $bloodType
     * @return JavIdol
     */
    public function setBloodType(?string $bloodType): self
    {
        $this->bloodType = $bloodType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     * @return JavIdol
     */
    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @param int|null $height
     * @return JavIdol
     */
    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFavorite(): ?int
    {
        return $this->favorite;
    }

    /**
     * @param int|null $favorite
     * @return JavIdol
     */
    public function setFavorite(?int $favorite): self
    {
        $this->favorite = $favorite;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBreast(): ?int
    {
        return $this->breast;
    }

    /**
     * @param int|null $breast
     * @return JavIdol
     */
    public function setBreast(?int $breast): self
    {
        $this->breast = $breast;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWaist(): ?int
    {
        return $this->waist;
    }

    /**
     * @param int|null $waist
     * @return JavIdol
     */
    public function setWaist(?int $waist): self
    {
        $this->waist = $waist;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getHips(): ?int
    {
        return $this->hips;
    }

    /**
     * @param int|null $hips
     * @return JavIdol
     */
    public function setHips(?int $hips): self
    {
        $this->hips = $hips;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdated(): ?DateTimeInterface
    {
        return $this->updated;
    }

    /**
     * @param DateTimeInterface $updated
     * @return JavIdol
     */
    public function setUpdated(DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }
}
