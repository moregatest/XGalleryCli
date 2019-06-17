<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="jav_r18_movies")
 * @ORM\Entity(repositoryClass="App\Repository\JavR18MovieRepository")
 */
class JavR18Movie
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $link;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $release_date;

    /**
     * @ORM\Column(type="string", length=125, nullable=true)
     */
    private $content_id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $urls;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return JavR18Movie
     */
    public function setUpdated(DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return JavR18Movie
     */
    public function setLink(string $link): self
    {
        $this->link = $link;

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
     * @param DateTimeInterface $release_date
     * @return JavR18Movie
     */
    public function setReleaseDate(DateTimeInterface $release_date): self
    {
        $this->release_date = $release_date;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getContentId(): ?string
    {
        return $this->content_id;
    }

    /**
     * @param string $content_id
     * @return JavR18Movie
     */
    public function setContentId(string $content_id): self
    {
        $this->content_id = $content_id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrls(): ?string
    {
        return $this->urls;
    }

    /**
     * @param string|null $urls
     * @return JavR18Movie
     */
    public function setUrls(?string $urls): self
    {
        $this->urls = $urls;

        return $this;
    }
}
