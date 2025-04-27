<?php

namespace App\Entity;

use App\Repository\TeacherRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeacherRepository::class)]
class Teacher extends User
{

    #[ORM\Column(length: 128)]
    private ?string $city = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2)]
    private ?string $pricePerLesson = null;

    #[ORM\ManyToMany(targetEntity: Language::class, inversedBy: 'teachers')]
    private Collection $languages;

    public function __construct()
    {
        parent::__construct();
        $this->languages = new ArrayCollection();
    }


    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getPricePerLesson(): ?string
    {
        return $this->pricePerLesson;
    }

    public function setPricePerLesson(string $pricePerLesson): self
    {
        $this->pricePerLesson = $pricePerLesson;
        return $this;
    }

    public function getType(): string
    {
        return 'teacher';
    }

    /**
     * @return Collection<int, Language>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(Language $language): static
    {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
        }

        return $this;
    }

    public function removeLanguage(Language $language): static
    {
        $this->languages->removeElement($language);

        return $this;
    }
}
