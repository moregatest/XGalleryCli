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
 * @ORM\Table(name="deliverynow_categories")
 * @ORM\Entity(repositoryClass="App\Repository\DeliveryNowCategoriesRepository")
 */
class DeliveryNowCategory
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=125)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $parentCategoryId;

    /**
     * @ORM\Column(type="string", length=125)
     */
    private $urlRewriteName;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return DeliveryNowCategory
     */
    public function setId(?int $id): self
    {
        $this->id = $id;

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
     * @return DeliveryNowCategory
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

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
     * @return DeliveryNowCategory
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getParentCategoryId(): ?int
    {
        return $this->parentCategoryId;
    }

    /**
     * @param int $parentCategoryId
     * @return DeliveryNowCategory
     */
    public function setParentCategoryId(int $parentCategoryId): self
    {
        $this->parentCategoryId = $parentCategoryId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrlRewriteName(): ?string
    {
        return $this->urlRewriteName;
    }

    /**
     * @param string $urlRewriteName
     * @return DeliveryNowCategory
     */
    public function setUrlRewriteName(string $urlRewriteName): self
    {
        $this->urlRewriteName = $urlRewriteName;

        return $this;
    }
}
