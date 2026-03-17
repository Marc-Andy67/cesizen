<?php

namespace App\Entity;

use App\Repository\ResponseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ResponseRepository::class)]
class Response
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column]
    private ?int $points = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'responses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    /**
     * @var Collection<int, Assessment>
     */
    #[ORM\ManyToMany(targetEntity: Assessment::class, mappedBy: 'responses')]
    private Collection $assessments;

    public function __construct()
    {
        $this->assessments = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return Collection<int, Assessment>
     */
    public function getAssessments(): Collection
    {
        return $this->assessments;
    }

    public function addAssessment(Assessment $assessment): static
    {
        if (!$this->assessments->contains($assessment)) {
            $this->assessments->add($assessment);
            $assessment->addResponse($this);
        }

        return $this;
    }

    public function removeAssessment(Assessment $assessment): static
    {
        if ($this->assessments->removeElement($assessment)) {
            $assessment->removeResponse($this);
        }

        return $this;
    }
}
