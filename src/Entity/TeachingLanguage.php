<?php

namespace App\Entity;

use App\Repository\TeacherRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a teaching assignment for a specific language by a teacher along with its price.
 *
 */
#[ORM\Entity]
class TeachingLanguage
{
    /**
     * The teacher who teaches this language.
     * This and `language` together form a composite primary key.
     */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Teacher::class, inversedBy: 'teachingLanguages')]
    private Teacher $teacher;

    /**
     * The language being taught.
     * This and `teacher` together form a composite primary key.
     */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Language::class, inversedBy: 'teachingLanguages')]
    private Language $language;

    /**
     * Price per lesson in this language in PLN (e.g. 50.00).
     */
    #[ORM\Column(type: 'decimal', precision: 6, scale: 2)]
    private ?float $price = null;

    /**
     * Gets the teacher who teaches this language.
     */
    public function getTeacher(): Teacher
    {
        return $this->teacher;
    }

    /**
     * Assigns a teacher to this teaching language.
     */
    public function setTeacher(Teacher $teacher): static
    {
        $this->teacher = $teacher;
        return $this;
    }

    /**
     * Gets the language that is being taught.
     */
    public function getLanguage(): Language
    {
        return $this->language;
    }

    /**
     * Sets the language being taught.
     */
    public function setLanguage(Language $language): static
    {
        $this->language = $language;
        return $this;
    }

    /**
     * Gets the price per lesson for this language.
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Sets the price per lesson for this language.
     */
    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }


}
