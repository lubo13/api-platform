<?php

/**
 * @package
 * @author  Lubo Grozdanov <grozdanov.lubo@gmail.com>
 */

declare(strict_types=1);

namespace App\Entity\Service;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(shortName="ServiceCarwash")
 * @ORM\Entity()
 * @ORM\Table(name="service_carwash")
 */
class Carwash extends Service
{
    /**
     * @ORM\Column(type="integer")
     */
    private int $vehicleCapacity;

    /**
     * @ApiProperty(description="You can relax in our wonderful coffee until we wash your car")
     * @ORM\Column(type="boolean")
     */
    private bool $coffeeShop;

    public function getVehicleCapacity(): int
    {
        return $this->vehicleCapacity;
    }

    public function setVehicleCapacity(int $vehicleCapacity): void
    {
        $this->vehicleCapacity = $vehicleCapacity;
    }

    public function isCoffeeShop(): bool
    {
        return $this->coffeeShop;
    }

    public function setCoffeeShop(bool $coffeeShop): void
    {
        $this->coffeeShop = $coffeeShop;
    }
}
