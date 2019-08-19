<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="deliverynow_booking_services")
 * @ORM\Entity(repositoryClass="App\Repository\DeliveryNowServiceRepository")
 */
class DeliveryNowService
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=125)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=125)
     */
    private $callCenter;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $url;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return DeliveryNowService
     */
    public function setId(?int $id): self
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
     * @return DeliveryNowService
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCallCenter(): ?string
    {
        return $this->callCenter;
    }

    /**
     * @param string $callCenter
     * @return DeliveryNowService
     */
    public function setCallCenter(string $callCenter): self
    {
        $this->callCenter = $callCenter;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return DeliveryNowService
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

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
     * @param string $url
     * @return DeliveryNowService
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
