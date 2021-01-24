<?php

/**
 * @package
 * @author  Lubo Grozdanov <grozdanov.lubo@gmail.com>
 */

declare(strict_types=1);

namespace App\Entity\Vehicle;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Schedule\Schedule;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *     shortName="VehicleJeep",
 *     collectionOperations={
 *         "post",
 *         "get",
 *     },
 *     itemOperations={
 *         "get",
 *         "put",
 *     }
 * )
 * @ORM\Entity
 */
class Jeep extends Vehicle
{

}
