<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JavMoviesXrefRepository")
 */
class JavMoviesXref
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $xref_id;

    /**
     * @ORM\Column(type="string", length=125)
     */
    private $xref_type;

    /**
     * @ORM\Column(type="integer")
     */
    private $movie_id;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getXrefId(): ?int
    {
        return $this->xref_id;
    }

    /**
     * @param int $xref_id
     * @return JavMoviesXref
     */
    public function setXrefId(int $xref_id): self
    {
        $this->xref_id = $xref_id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getXrefType(): ?string
    {
        return $this->xref_type;
    }

    /**
     * @param string $xref_type
     * @return JavMoviesXref
     */
    public function setXrefType(string $xref_type): self
    {
        $this->xref_type = $xref_type;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMovieId(): ?int
    {
        return $this->movie_id;
    }

    /**
     * @param int $movie_id
     * @return JavMoviesXref
     */
    public function setMovieId(int $movie_id): self
    {
        $this->movie_id = $movie_id;

        return $this;
    }
}
