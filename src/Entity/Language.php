<?php

namespace App\Entity;

use App\Repository\LanguageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a language that can be taught by teachers or learned by students.
 */
#[ORM\Entity(repositoryClass: LanguageRepository::class)]
class Language
{
    /**
     * Unique identifier of the language.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Name of the language (e.g. Spanish, Polish).
     * Has to be unique.
     */
    #[ORM\Column(length: 64, unique: true)]
    private ?string $name = null;

    /**
     * List of TeachingLanguage relations where this language is being taught by teachers.
     *
     * @var Collection<int, TeachingLanguage>
     */
    #[ORM\OneToMany(targetEntity: TeachingLanguage::class, mappedBy: 'language', cascade: ['persist', 'remove'])]
    private Collection $teachingLanguages;

    /**
     * List of students currently learning this language.
     * Mapped by 'learningLanguage' property in the Student entity.
     *
     * @var Collection<int, Student>
     */
    #[ORM\OneToMany(targetEntity: Student::class, mappedBy: 'learningLanguage')]
    private Collection $students;

    public function __construct()
    {
        $this->teachingLanguages = new ArrayCollection();
        $this->students = new ArrayCollection();
    }


    /**
     * Gets ID of this language.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gets name of this language.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets name of this language.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }



    /**
     *  Gets teachings assignments for this language.
     * @return Collection<int, TeachingLanguage>
     */
    public function getTeachingLanguages(): Collection
    {
        return $this->teachingLanguages;
    }

    /**
     * Adds a teaching assignment for this language.
     *
     * @param TeachingLanguage $teachingLanguage
     * @return $this
     */
    public function addTeachingLanguage(TeachingLanguage $teachingLanguage): static
    {
        if (!$this->teachingLanguages->contains($teachingLanguage)) {
            $this->teachingLanguages->add($teachingLanguage);
            $teachingLanguage->setLanguage($this);
        }
        return $this;
    }

    /**
     * Removes a teaching assignment of this language.
     *
     * @param TeachingLanguage $teachingLanguage
     * @return $this
     */
    public function removeTeachingLanguage(TeachingLanguage $teachingLanguage): static
    {
        $this->teachingLanguages->removeElement($teachingLanguage);
        return $this;
    }

    /**
     * Gets students that are currently learning this language.
     *
     * @return Collection<int, Student>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    /**
     * Assigns this language to a student as their learning language.
     */
    public function addStudent(Student $student): static
    {
        if (!$this->students->contains($student)) {
            $this->students->add($student);
            $student->setLearningLanguage($this);
        }
        return $this;
    }

    /**
     * Removes this language as a learning language of the student.
     *
     */
    public function removeStudent(Student $student): static
    {
        if ($this->students->removeElement($student)) {
            $student->setLearningLanguage(null);
        }
        return $this;
    }
}
