<?php

/**
 * @package
 * @author  Lubo Grozdanov <grozdanov.lubo@gmail.com>
 */

declare(strict_types=1);

namespace App\Tests\Api\Functional\Schedule;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Schedule\Schedule;
use App\Entity\Service\Service;
use App\Entity\Vehicle\Vehicle;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class PutScheduleTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private static array $schedules = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::bootKernel();
        $objectManager = static::$container->get('doctrine')->getManagerForClass(Schedule::class);
        self::$schedules = $objectManager->getRepository(Schedule::class)->findAll();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::$schedules = [];
    }

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
    }

    public function testPutAllDataSucceeds(): void
    {
        $serviceIri = $this->findIriBy(Service::class, ['name' => 'Carwash 1']);
        $vehicleIri = $this->findIriBy(Vehicle::class, ['model' => '520']);
        $datetime = new \DateTime();
        foreach (self::$schedules as $k => $schedule) {
            $scheduleIri = static::$container->get('api_platform.iri_converter')->getIriFromItem($schedule);
            $response = static::createClient()->request(
                'PUT',
                $scheduleIri,
                [
                    'json' => [
                        'datetime' => $datetime->format('c'),
                        'service' => $serviceIri,
                        'vehicle' => $vehicleIri,
                    ],
                ]
            );
            $this->assertResponseStatusCodeSame(200);
            $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

            $this->assertJsonContains(
                '
                {
                    "@context": "/contexts/Schedule",
                    "@type": "Schedule",
                    "@id": "'.$scheduleIri.'",
                    "datetime": "'.$datetime->format('c').'",
                    "vehicle": "'.$vehicleIri.'",
                    "service": "'.$serviceIri.'"
                }
            '
            );
        }
    }

    public function testPutServiceSucceeds(): void
    {
        $serviceIri = $this->findIriBy(Service::class, ['name' => 'Carwash 1']);
        $datetime = new \DateTime();
        foreach (self::$schedules as $k => $schedule) {
            $vehicleIri = static::$container->get('api_platform.iri_converter')->getIriFromItem($schedule->getVehicle());
            $scheduleIri = static::$container->get('api_platform.iri_converter')->getIriFromItem($schedule);
            $response = static::createClient()->request(
                'PUT',
                $scheduleIri,
                [
                    'json' => [
                        'datetime' => $datetime->format('c'),
                        'service' => $serviceIri,
                    ],
                ]
            );
            $this->assertResponseStatusCodeSame(200);
            $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

            $this->assertJsonContains(
                '
                {
                    "@context": "/contexts/Schedule",
                    "@type": "Schedule",
                    "@id": "'.$scheduleIri.'",
                    "datetime": "'.$datetime->format('c').'",
                    "vehicle": "'.$vehicleIri.'",
                    "service": "'.$serviceIri.'"
                }
            '
            );
        }
    }

    public function testPutVehicleSucceeds(): void
    {
        $vehicleIri = $this->findIriBy(Vehicle::class, ['model' => '520']);
        $datetime = new \DateTime();
        foreach (self::$schedules as $k => $schedule) {
            $serviceIri = static::$container->get('api_platform.iri_converter')->getIriFromItem($schedule->getService());
            $scheduleIri = static::$container->get('api_platform.iri_converter')->getIriFromItem($schedule);
            $response = static::createClient()->request(
                'PUT',
                $scheduleIri,
                [
                    'json' => [
                        'datetime' => $datetime->format('c'),
                        'vehicle' => $vehicleIri,
                    ],
                ]
            );
            $this->assertResponseStatusCodeSame(200);
            $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

            $this->assertJsonContains(
                '
                {
                    "@context": "/contexts/Schedule",
                    "@type": "Schedule",
                    "@id": "'.$scheduleIri.'",
                    "datetime": "'.$datetime->format('c').'",
                    "vehicle": "'.$vehicleIri.'",
                    "service": "'.$serviceIri.'"
                }
            '
            );
        }
    }
}
