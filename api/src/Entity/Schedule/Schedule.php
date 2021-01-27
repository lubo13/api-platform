<?php

namespace App\Entity\Schedule;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Service\Service;
use App\Entity\Vehicle\Vehicle;
use App\Repository\ScheduleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"output"}},
 *     denormalizationContext={"groups"={"input"}},
 *     collectionOperations={
 *         "post",
 *         "get",
 *     },
 *     itemOperations={
 *         "get",
 *         "put",
 *         "patch",
 *     }
 *  )
 * @ORM\Entity(repositoryClass=ScheduleRepository::class)
 */
class Schedule
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"input", "output"})
     */
    private int $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"input", "output"})
     */
    private \DateTimeInterface $datetime;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicle::class, inversedBy="schedules")
     * @Groups({"input", "output"})
     */
    private ?Vehicle $vehicle;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="schedules")
     * @Groups({"input", "output"})
     */
    private ?Service $service;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatetime(): ?\DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTimeInterface $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }
}
