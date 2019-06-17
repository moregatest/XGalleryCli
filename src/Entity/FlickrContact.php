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
 * FlickrContact
 *
 * @ORM\Table(name="flickr_contacts", uniqueConstraints={@ORM\UniqueConstraint(name="nsid", columns={"nsid", "username"})}, indexes={@ORM\Index(name="nsid_idx", columns={"nsid"})})
 * @ORM\Entity(repositoryClass="App\Repository\FlickrContactRepository")
 */
class FlickrContact
{
    /**
     * @var string
     *
     * @ORM\Column(name="nsid", type="string", length=125, nullable=false)
     * @ORM\Id
     */
    private $nsid;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=125, nullable=false)
     */
    private $username;

    /**
     * @var int|null
     *
     * @ORM\Column(name="iconserver", type="integer", nullable=true)
     */
    private $iconserver;

    /**
     * @var int|null
     *
     * @ORM\Column(name="iconfarm", type="integer", nullable=true)
     */
    private $iconfarm;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="ignored", type="boolean", nullable=true)
     */
    private $ignored;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="rev_ignored", type="boolean", nullable=true)
     */
    private $revIgnored;

    /**
     * @var string|null
     *
     * @ORM\Column(name="realname", type="string", length=255, nullable=true)
     */
    private $realname;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="friend", type="boolean", nullable=true)
     */
    private $friend;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="family", type="boolean", nullable=true)
     */
    private $family;

    /**
     * @var string|null
     *
     * @ORM\Column(name="path_alias", type="string", length=255, nullable=true)
     */
    private $pathAlias;

    /**
     * @var string|null
     *
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var int|null
     *
     * @ORM\Column(name="photos", type="integer", nullable=true)
     */
    private $photos;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @return string|null
     */
    public function getNsid(): ?string
    {
        return $this->nsid;
    }

    /**
     * @param string $nsid
     * @return FlickrContact
     */
    public function setNsid(string $nsid): self
    {
        $this->nsid = $nsid;

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
     * @return FlickrContact
     */
    public function setCreated(DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return FlickrContact
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getIconserver(): ?int
    {
        return $this->iconserver;
    }

    /**
     * @param int|null $iconserver
     * @return FlickrContact
     */
    public function setIconserver(?int $iconserver): self
    {
        $this->iconserver = $iconserver;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getIconfarm(): ?int
    {
        return $this->iconfarm;
    }

    /**
     * @param int|null $iconfarm
     * @return FlickrContact
     */
    public function setIconfarm(?int $iconfarm): self
    {
        $this->iconfarm = $iconfarm;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIgnored(): ?bool
    {
        return $this->ignored;
    }

    /**
     * @param bool|null $ignored
     * @return FlickrContact
     */
    public function setIgnored(?bool $ignored): self
    {
        $this->ignored = $ignored;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getRevIgnored(): ?bool
    {
        return $this->revIgnored;
    }

    /**
     * @param bool|null $revIgnored
     * @return FlickrContact
     */
    public function setRevIgnored(?bool $revIgnored): self
    {
        $this->revIgnored = $revIgnored;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRealname(): ?string
    {
        return $this->realname;
    }

    /**
     * @param string|null $realname
     * @return FlickrContact
     */
    public function setRealname(?string $realname): self
    {
        $this->realname = $realname;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getFriend(): ?bool
    {
        return $this->friend;
    }

    /**
     * @param bool|null $friend
     * @return FlickrContact
     */
    public function setFriend(?bool $friend): self
    {
        $this->friend = $friend;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getFamily(): ?bool
    {
        return $this->family;
    }

    /**
     * @param bool|null $family
     * @return FlickrContact
     */
    public function setFamily(?bool $family): self
    {
        $this->family = $family;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPathAlias(): ?string
    {
        return $this->pathAlias;
    }

    /**
     * @param string|null $pathAlias
     * @return FlickrContact
     */
    public function setPathAlias(?string $pathAlias): self
    {
        $this->pathAlias = $pathAlias;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string|null $location
     * @return FlickrContact
     */
    public function setLocation(?string $location): self
    {
        $this->location = $location;

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
     * @return FlickrContact
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPhotos(): ?int
    {
        return $this->photos;
    }

    /**
     * @param int|null $photos
     * @return FlickrContact
     */
    public function setPhotos(?int $photos): self
    {
        $this->photos = $photos;

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
     * @return FlickrContact
     */
    public function setUpdated(DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }
}
