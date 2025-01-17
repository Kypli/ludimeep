<?php

namespace App\Entity;

use App\Repository\SeanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SeanceRepository::class)
 */
class Seance
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $duree;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity=SeanceType::class, inversedBy="seances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=SeanceLieu::class, inversedBy="seances")
     */
    private $lieu;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="seances")
     */
    private $presents;

    /**
     * @ORM\OneToMany(targetEntity=Table::class, mappedBy="seance", orphanRemoval=true)
     */
    private $tables;

    public function __construct()
    {
        $this->presents = new ArrayCollection();
        $this->tables = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDuree(): ?\DateTimeInterface
    {
        return $this->duree;
    }

    public function setDuree(?\DateTimeInterface $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getType(): ?SeanceType
    {
        return $this->type;
    }

    public function setType(?SeanceType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLieu(): ?SeanceLieu
    {
        return $this->lieu;
    }

    public function setLieu(?SeanceLieu $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getPresents(): Collection
    {
        return $this->presents;
    }

    public function addPresent(User $present): self
    {
        if (!$this->presents->contains($present)) {
            $this->presents[] = $present;
        }

        return $this;
    }

    public function removePresent(User $present): self
    {
        $this->presents->removeElement($present);

        return $this;
    }

    /**
     * @return Collection<int, Table>
     */
    public function getTables(): Collection
    {
        return $this->tables;
    }

    public function addTable(Table $table): self
    {
        if (!$this->tables->contains($table)) {
            $this->tables[] = $table;
            $table->setSeance($this);
        }

        return $this;
    }

    public function removeTable(Table $table): self
    {
        if ($this->tables->removeElement($table)) {
            // set the owning side to null (unless already changed)
            if ($table->getSeance() === $this) {
                $table->setSeance(null);
            }
        }

        return $this;
    }
}
