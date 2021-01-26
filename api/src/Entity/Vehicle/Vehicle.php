<?php

namespace App\Entity\Vehicle;

use App\Entity\DynamicRelationInterface;
use App\Entity\Schedule\Schedule;
use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;

/**
 * @ORM\Entity(repositoryClass=VehicleRepository::class)
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @DiscriminatorMap({"car" = "Car", "jeep" = "Jeep", "bus" = "Bus", "truck" = "Truck"})
 * @ORM\Table(name="vehicle")
 */
abstract class Vehicle implements DynamicRelationInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\GeneratedValue
     * @ORM\Column(type="string")
     */
    private string $tradeMark;

    /**
     * @ORM\GeneratedValue
     * @ORM\Column(type="string")
     */
    private string $model;

    /**
     * @ORM\OneToMany(targetEntity=Schedule::class, mappedBy="vehicle")
     */
    private Collection $schedules;

    public function __construct()
    {
        $this->schedules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTradeMark(): string
    {
        return $this->tradeMark;
    }

    public function setTradeMark(string $tradeMark): void
    {
        $this->tradeMark = $tradeMark;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    /**
     * @return Collection|Schedule[]
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }
}
