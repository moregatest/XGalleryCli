<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="jav_downloads")
 * @ORM\Entity(repositoryClass="App\Repository\JavDownloadRepository")
 */
class JavDownload
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=50)
     */
    private $item_number;

    public function getItemNumber(): ?string
    {
        return $this->item_number;
    }

    public function setItemNumber(string $item_number): self
    {
        $this->item_number = $item_number;

        return $this;
    }
}
