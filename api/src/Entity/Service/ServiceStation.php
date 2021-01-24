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
 * @ApiResource(
 *     shortName="ServiceServiceStation",
 *     collectionOperations={
 *         "post",
 *         "get",
 *     },
 *     itemOperations={
 *         "get",
 *         "put",
 *     }
 *  )
 * @ORM\Entity()
 * @ORM\Table(name="service_service_station")
 */
class ServiceStation extends Service
{
    /**
     * @ApiProperty(description="Available roadside assistance")
     * @ORM\Column(type="boolean")
     */
    private bool $roadsideAssistance;

    /**
     * @ApiProperty(description="Available free diagnostic")
     * @ORM\Column(type="boolean")
     */
    private bool $freeDiagnostic;

    /**
     * @ApiProperty(description="Available express service")
     * @ORM\Column(type="boolean")
     */
    private bool $expressService;

    public function isRoadsideAssistance(): bool
    {
        return $this->roadsideAssistance;
    }

    public function setRoadsideAssistance(bool $roadsideAssistance): void
    {
        $this->roadsideAssistance = $roadsideAssistance;
    }

    public function isFreeDiagnostic(): bool
    {
        return $this->freeDiagnostic;
    }

    public function setFreeDiagnostic(bool $freeDiagnostic): void
    {
        $this->freeDiagnostic = $freeDiagnostic;
    }

    public function isExpressService(): bool
    {
        return $this->expressService;
    }

    public function setExpressService(bool $expressService): void
    {
        $this->expressService = $expressService;
    }
}
