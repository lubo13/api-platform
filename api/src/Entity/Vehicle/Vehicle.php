<?php

namespace App\Entity\Vehicle;

use App\Repository\VehicleRepository;
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
abstract class Vehicle
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
}
