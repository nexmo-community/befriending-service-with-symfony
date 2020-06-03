<?php

namespace App\Entity;

use App\Repository\MatchRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=MatchRepository::class)
 * @ORM\Table(name="`match`")
 */
class Match
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="matches")
     * @ORM\JoinColumn(nullable=false)
     */
    private $callerOne;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="matches")
     * @ORM\JoinColumn(nullable=false)
     */
    private $callerTwo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $conferenceName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCallerOne(): ?User
    {
        return $this->callerOne;
    }

    public function setCallerOne(?User $callerOne): self
    {
        $this->callerOne = $callerOne;

        return $this;
    }

    public function getCallerTwo(): ?User
    {
        return $this->callerTwo;
    }

    public function setCallerTwo(?User $callerTwo): self
    {
        $this->callerTwo = $callerTwo;

        return $this;
    }

    public function getConferenceName(): ?string
    {
        return $this->conferenceName;
    }

    public function setConferenceName(string $conferenceName): self
    {
        $this->conferenceName = $conferenceName;

        return $this;
    }
}
