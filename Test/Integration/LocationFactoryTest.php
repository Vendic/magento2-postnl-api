<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Test\Integration;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Vendic\PostnlApi\Api\Data\LocationInterfaceFactory;

class LocationFactoryTest extends TestCase
{
    public function testModelBuild() : void
    {
        $locationData = [
            'Address' => [
                'City' => 'Amsterdam',
                'Countrycode' => 'NL',
                'HouseNr' => '1',
                'HouseNrExt' => 'A',
                'Remark' => 'Remark',
                'Street' => 'Street',
                'Zipcode' => '1234AB'
            ],
            'DeliveryOptions' => [
                'string' => [
                    'DO',
                    'PG',
                    'PBA'
                ]
            ],
            'Distance' => '1368',
            'Latitude' => '52.370216',
            'Longitude' => '4.895168',
            'Name' => 'Pakketautomaat',
            'LocationCode' => '123456',
            'OpeningHours' => [
                'Monday' => [
                    'string' => [
                        '09:00-18:00'
                    ]
                ],
                'Tuesday' => [
                    'string' => [
                        '09:00-18:00'
                    ]
                ],
                'Wednesday' => [
                    'string' => [
                        '09:00-18:00'
                    ]
                ],
                'Thursday' => [
                    'string' => [
                        '09:00-18:00'
                    ]
                ],
                'Friday' => [
                    'string' => [
                        '09:00-18:00'
                    ]
                ],
                'Saturday' => [
                    'string' => [
                        '09:00-18:00'
                    ]
                ],
                'Sunday' => [
                    'string' => [
                        '09:00-18:00'
                    ]
                ]
            ],
            'PartnerName' => 'PostNL',
            'RetailNetworkID' => 'PNPNL-01',
        ];

        /** @var LocationInterfaceFactory $locationFactory */
        $locationFactory = Bootstrap::getObjectManager()->get(LocationInterfaceFactory::class);
        $location = $locationFactory->create(['data' => $locationData]);

        $this->assertEquals($locationData['Name'], $location->getName());
        $this->assertEquals($locationData['RetailNetworkID'], $location->getRetailNetworkID());

        $this->assertEquals($locationData['Address']['City'], $location->getAddress()->getCity());
        $this->assertEquals($locationData['Address']['Countrycode'], $location->getAddress()->getCountryCode());
        $this->assertEquals($locationData['Address']['HouseNr'], $location->getAddress()->getHouseNr());
        $this->assertIsArray($location->getAddress()->toArray());
    }

}
