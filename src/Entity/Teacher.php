<?php

namespace App\Entity;

use App\Repository\TeacherRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a teacher user in the system.
 * Inherits base user information from the abstract User class.
 */
#[ORM\Entity]
class Teacher extends User
{

    /**
     * City where the teacher is located.
     */
    #[ORM\Column(length: 128, nullable: true)]
    private ?string $city = null;

    /**
     * Default price per lesson in PLN (e.g. 50.00).
     * Is used as a default price if not set in 'teachingLanguage'.
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    private ?string $pricePerLesson = null;

    /**
     * List of TeachingLanguage relations where this teacher is teaching a language.
     *
     * @var Collection<int, TeachingLanguage>
     */
    #[ORM\OneToMany(mappedBy: 'teacher', targetEntity: TeachingLanguage::class, cascade: ['persist', 'remove'])]
    private Collection $teachingLanguages;

    public function __construct()
    {
        parent::__construct();
        $this->teachingLanguages  = new ArrayCollection();
    }


    /**
     * Gets a city.
     *
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Sets the teacher's city.
     *
     * @param string $city
     * @return $this
     * */
    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Gets price per lesson.
     *
     * @return string|null
     */
    public function getPricePerLesson(): ?string
    {
        return $this->pricePerLesson;
    }

    /**
     * Sets price per lesson.
     *
     * @param string $pricePerLesson
     * @return $this
     */
    public function setPricePerLesson(string $pricePerLesson): self
    {
        $this->pricePerLesson = $pricePerLesson;
        return $this;
    }

    /**
     * Returns the type identifier for this user,
     * distinguish between user types.
     */
    public function getType(): string
    {
        return 'teacher';
    }

    /**
     * Gets teaching assignments for this teacher.
     *
     * @var Collection<int, TeachingLanguage>
     */
    public function getTeachingLanguages(): Collection
    {
        return $this->teachingLanguages;
    }

    /**
     *
     *  Adds a teaching assignment for a specific language to this teacher.
     *
     * @param TeachingLanguage $teachingLanguage
     * @return $this
     */
    public function addTeachingLanguage(TeachingLanguage $teachingLanguage): static
    {
        if (!$this->teachingLanguages->contains($teachingLanguage)) {
            $this->teachingLanguages->add($teachingLanguage);
            $teachingLanguage->setTeacher($this);
        }
        return $this;
    }

    /**
     *  Removes a teaching assignment associated with this teacher.
     *
     * @param TeachingLanguage $teachingLanguage
     * @return $this
     */
    public function removeTeachingLanguage(TeachingLanguage $teachingLanguage): static
    {
        $this->teachingLanguages->removeElement($teachingLanguage);
        return $this;
    }
}
