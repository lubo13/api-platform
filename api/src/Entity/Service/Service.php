<?php

namespace App\Entity\Service;

use App\Entity\DynamicRelationInterface;
use App\Entity\Schedule\Schedule;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\InheritanceType;

/**
 * @ORM\Entity(repositoryClass=ServiceRepository::class)
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @DiscriminatorMap({"carwash" = "Carwash", "service_station" = "ServiceStation", "tire_shop" = "TireShop"})
 * @ORM\Table(name="service")
 */
abstract class Service implements DynamicRelationInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private string $name;

    /**
     * @ORM\Column(type="string")
     */
    private string $workingTimeStart;

    /**
     * @ORM\Column(type="string")
     */
    private string $workingTimeEnd;

    /**
     * @ORM\OneToMany(targetEntity=Schedule::class, mappedBy="service")
     */
    private Collection $schedules;

    public function __construct()
    {
        $this->schedules = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkingTimeStart(): string
    {
        return $this->workingTimeStart;
    }

    public function setWorkingTimeStart(string $workingTimeStart): void
    {
        $this->workingTimeStart = $workingTimeStart;
    }

    public function getWorkingTimeEnd(): string
    {
        return $this->workingTimeEnd;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setWorkingTimeEnd(string $workingTimeEnd): void
    {
        $this->workingTimeEnd = $workingTimeEnd;
    }

    /**
     * @return Collection|Schedule[]
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }
}
