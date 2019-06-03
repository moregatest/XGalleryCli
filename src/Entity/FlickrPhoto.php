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
 * FlickrPhoto
 *
 * @ORM\Table(name="flickr_photo", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"}), @ORM\UniqueConstraint(name="unique_idx", columns={"id", "owner", "secret", "server", "farm"})})
 * @ORM\Entity(repositoryClass="App\Repository\FlickrPhotoRepository")
 */
class FlickrPhoto
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string", length=125, nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="owner", type="string", length=125, nullable=false)
     */
    private $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="secret", type="string", length=25, nullable=false)
     */
    private $secret;

    /**
     * @var int
     *
     * @ORM\Column(name="server", type="integer", nullable=false)
     */
    private $server;

    /**
     * @var int
     *
     * @ORM\Column(name="farm", type="smallint", nullable=false)
     */
    private $farm;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="ispublic", type="boolean", nullable=true)
     */
    private $ispublic;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="isfriend", type="boolean", nullable=true)
     */
    private $isfriend;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="isfamily", type="boolean", nullable=true)
     */
    private $isfamily;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var bool|null
     *
     * @ORM\Column(name="status", type="integer", length=4, nullable=true)
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(name="urls", type="text", length=65535, nullable=true)
     */
    private $urls;

    /**
     * @var int
     *
     * @ORM\Column(name="width", type="integer", nullable=true)
     */
    private $width;

    /**
     * @var int
     *
     * @ORM\Column(name="height", type="integer", nullable=true)
     */
    private $height;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="media", type="boolean", nullable=true)
     */
    private $media;

    /**
     * @var string|null
     *
     * @ORM\Column(name="params", type="text", length=65535, nullable=true)
     */
    private $params;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return FlickrPhoto
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOwner(): ?string
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     * @return FlickrPhoto
     */
    public function setOwner(string $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSecret(): ?string
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     * @return FlickrPhoto
     */
    public function setSecret(string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getServer(): ?int
    {
        return $this->server;
    }

    /**
     * @param int $server
     * @return FlickrPhoto
     */
    public function setServer(int $server): self
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFarm(): ?int
    {
        return $this->farm;
    }

    /**
     * @param int $farm
     * @return FlickrPhoto
     */
    public function setFarm(int $farm): self
    {
        $this->farm = $farm;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return FlickrPhoto
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIspublic(): ?bool
    {
        return $this->ispublic;
    }

    /**
     * @param bool|null $ispublic
     * @return FlickrPhoto
     */
    public function setIspublic(?bool $ispublic): self
    {
        $this->ispublic = $ispublic;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsfriend(): ?bool
    {
        return $this->isfriend;
    }

    /**
     * @param bool|null $isfriend
     * @return FlickrPhoto
     */
    public function setIsfriend(?bool $isfriend): self
    {
        $this->isfriend = $isfriend;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsfamily(): ?bool
    {
        return $this->isfamily;
    }

    /**
     * @param bool|null $isfamily
     * @return FlickrPhoto
     */
    public function setIsfamily(?bool $isfamily): self
    {
        $this->isfamily = $isfamily;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getCreated(): ?DateTimeInterface
    {
        return $this->created;
    }

    /**
     * @param DateTimeInterface $created
     * @return FlickrPhoto
     */
    public function setCreated(DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @param int|null $status
     * @return FlickrPhoto
     */
    public function setStatus(?int $status): self
    {
        $this->status = $status;

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
     * @return FlickrPhoto
     */
    public function setUrls(?string $urls): self
    {
        $this->urls = $urls;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return FlickrPhoto
     */
    public function setWidth(int $width): self
    {
        $this->width = $width;

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
     * @param int $height
     * @return FlickrPhoto
     */
    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return FlickrPhoto
     */
    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getMedia(): ?bool
    {
        return $this->media;
    }

    /**
     * @param bool|null $media
     * @return FlickrPhoto
     */
    public function setMedia(?bool $media): self
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getParams(): ?string
    {
        return $this->params;
    }

    /**
     * @param string|null $params
     * @return FlickrPhoto
     */
    public function setParams(?string $params): self
    {
        $this->params = $params;

        return $this;
    }
}
