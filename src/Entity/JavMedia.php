<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="jav_media")
 * @ORM\Entity(repositoryClass="App\Repository\JavMediaRepository")
 */
class JavMedia
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=125, nullable=true)
     */
    private $codec_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $codec_long_name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $width;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $duration;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bit_rate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bits_per_raw_sample;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nb_frames;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $directory;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $file_size;

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
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string|null $filename
     * @return JavMedia
     */
    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    /**
     * @param string|null $directory
     * @return JavMedia
     */
    public function setDirectory(?string $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodecName(): ?string
    {
        return $this->codec_name;
    }

    /**
     * @param string|null $codec_name
     * @return JavMedia
     */
    public function setCodecName(?string $codec_name): self
    {
        $this->codec_name = $codec_name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodecLongName(): ?string
    {
        return $this->codec_long_name;
    }

    /**
     * @param string|null $codec_long_name
     * @return JavMedia
     */
    public function setCodecLongName(?string $codec_long_name): self
    {
        $this->codec_long_name = $codec_long_name;

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
     * @param int|null $width
     * @return JavMedia
     */
    public function setWidth(?int $width): self
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
     * @param int|null $height
     * @return JavMedia
     */
    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getDuration(): ?float
    {
        return $this->duration;
    }

    /**
     * @param float|null $duration
     * @return JavMedia
     */
    public function setDuration(?float $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBitRate(): ?int
    {
        return $this->bit_rate;
    }

    /**
     * @param int|null $bit_rate
     * @return JavMedia
     */
    public function setBitRate(?int $bit_rate): self
    {
        $this->bit_rate = $bit_rate;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBitsPerRawSample(): ?int
    {
        return $this->bits_per_raw_sample;
    }

    /**
     * @param int|null $bits_per_raw_sample
     * @return JavMedia
     */
    public function setBitsPerRawSample(?int $bits_per_raw_sample): self
    {
        $this->bits_per_raw_sample = $bits_per_raw_sample;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getNbFrames(): ?int
    {
        return $this->nb_frames;
    }

    /**
     * @param int|null $nb_frames
     * @return JavMedia
     */
    public function setNbFrames(?int $nb_frames): self
    {
        $this->nb_frames = $nb_frames;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFileSize(): ?int
    {
        return $this->file_size;
    }

    /**
     * @param int|null $fileSize
     * @return JavMedia
     */
    public function setFileSize(?int $fileSize): self
    {
        $this->file_size = $fileSize;

        return $this;
    }
}
