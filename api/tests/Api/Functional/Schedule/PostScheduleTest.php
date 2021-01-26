<?php

/**
 * @package
 * @author  Lubo Grozdanov <grozdanov.lubo@gmail.com>
 */

declare(strict_types=1);

namespace App\Tests\Api\Functional\Schedule;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Service\Service;
use App\Entity\Vehicle\Vehicle;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class PostScheduleTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    protected function setUp(): void
    {
        static::bootKernel();
    }

    /**
     * @dataProvider provideData()
     */
    public function testCreateScheduleSucceeds($data): void
    {
        $serviceIri = $this->findIriBy(Service::class, ['name' => $data['service']]);
        $vehicleIri = $this->findIriBy(Vehicle::class, ['model' => $data['vehicle']]);

        $datetime = new \DateTime();
        $response = static::createClient()->request(
            'POST',
            '/schedules',
            [
                'json' => [
                    'datetime' => $datetime->format('c'),
                    'service' => $serviceIri,
                    'vehicle' => $vehicleIri,
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains(
            '
            {
                "@context": "/contexts/Schedule",
                "@type": "Schedule",
                "datetime": "'.$datetime->format('c').'",
                "vehicle": "'.$vehicleIri.'",
                "service": "'.$serviceIri.'"
            }
        '
        );
    }

    public function provideData(): array
    {
        return [
            'schedule_service_carwash_bus' => [
                [
                    'vehicle' => 'Veto',
                    'service' => 'Carwash 1',
                ],
            ],
            'schedule_service_carwash_car' => [
                [
                    'vehicle' => '520',
                    'service' => 'Carwash 2',
                ],
            ],
            'schedule_service_carwash_jeep' => [
                [
                    'vehicle' => 'GLK',
                    'service' => 'Carwash 3',
                ],
            ],
            'schedule_service_carwash_truck' => [
                [
                    'vehicle' => 'Magnum',
                    'service' => 'Carwash 4',
                ],
            ],

            'schedule_service_service_station_bus' => [
                [
                    'vehicle' => 'Veto',
                    'service' => 'Service Station 1',
                ],
            ],
            'schedule_service_service_station_car' => [
                [
                    'vehicle' => 'S6',
                    'service' => 'Service Station 2',
                ],
            ],
            'schedule_service_service_station_jeep' => [
                [
                    'vehicle' => 'X5',
                    'service' => 'Service Station 3',
                ],
            ],
            'schedule_service_service_station_truck' => [
                [
                    'vehicle' => 'P380',
                    'service' => 'Service Station 4',
                ],
            ],

            'schedule_service_tire_shop_bus' => [
                [
                    'vehicle' => 'Boxer',
                    'service' => 'Tire Shop 1',
                ],
            ],
            'schedule_service_tire_shop_car' => [
                [
                    'vehicle' => 'S-class',
                    'service' => 'Tire Shop 2',
                ],
            ],
            'schedule_service_tire_shop_jeep' => [
                [
                    'vehicle' => 'Q6',
                    'service' => 'Tire Shop 3',
                ],
            ],
            'schedule_service_tire_shop_truck' => [
                [
                    'vehicle' => 'Actros',
                    'service' => 'Tire Shop 4',
                ],
            ],
        ];
    }
}
