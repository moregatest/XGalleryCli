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

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="deliverynow_country")
 * @ORM\Entity(repositoryClass="App\Repository\DeliveryNowCountryRepository")
 */
class DeliveryNowCountry
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(name="two_letter_iso_code", type="string", length=5)
     */
    private $twoLetterIsoCode;

    /**
     * @ORM\Column(name="language_code", type="string", length=5)
     */
    private $languageCode;

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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return DeliveryNowCountry
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTwoLetterIsoCode(): ?string
    {
        return $this->twoLetterIsoCode;
    }

    /**
     * @param string $twoLetterIsoCode
     * @return DeliveryNowCountry
     */
    public function setTwoLetterIsoCode(string $twoLetterIsoCode): self
    {
        $this->twoLetterIsoCode = $twoLetterIsoCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    /**
     * @param string $languageCode
     * @return DeliveryNowCountry
     */
    public function setLanguageCode(string $languageCode): self
    {
        $this->languageCode = $languageCode;

        return $this;
    }
}
