<?php

namespace App\Entity\Service;

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
abstract class Service
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $workingTimeStart;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $workingTimeEnd;

    /**
     * @ORM\OneToMany(targetEntity=Schedule::class, mappedBy="service")
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

    public function getWorkingTimeStart(): \DateTimeInterface
    {
        return $this->workingTimeStart;
    }

    public function setWorkingTimeStart(\DateTimeInterface $workingTimeStart): void
    {
        $this->workingTimeStart = $workingTimeStart;
    }

    public function getWorkingTimeEnd(): \DateTimeInterface
    {
        return $this->workingTimeEnd;
    }

    public function setWorkingTimeEnd(\DateTimeInterface $workingTimeEnd): void
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
