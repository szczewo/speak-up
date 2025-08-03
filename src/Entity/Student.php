<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a student user in the system.
 * Inherits base user information from the abstract User class.
 */
#[ORM\Entity]
class Student extends User
{
    /**
     * The language that the student is currently learning.
     * Many-to-one relationship.
     */
    #[ORM\ManyToOne(targetEntity: Language::class, inversedBy: 'students')]
    private ?Language $learningLanguage = null;

    /**
     * Returns the type identifier for this user,
     * distinguish between user types.
     */
    public function getType(): string
    {
        return 'student';
    }

    /**
     *  Gets the language that the student is currently learning.
     *
     * @return Language|null
     */
    public function getLearningLanguage(): ?Language
    {
        return $this->learningLanguage;
    }

    /**
     *  Sets the language that the student is currently learning.
     *
     * @param Language|null $learningLanguage
     * @return static
     */
    public function setLearningLanguage(?Language $learningLanguage): static
    {
        $this->learningLanguage = $learningLanguage;
        return $this;
    }

}
