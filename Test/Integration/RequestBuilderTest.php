<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Test\Integration;

use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Vendic\PostnlApi\Api\Data\LocationInterface;
use Vendic\PostnlApi\Utils\RequestBuilder;

class RequestBuilderTest extends TestCase
{
    public function testBuildRequestForPickupSave() : void
    {
        $objectManager = Bootstrap::getInstance()->getObjectManager();

        $billingAddressData = [
            'country_id' => 'NL',
            'street' => [
                'Keulenstraat',
                '7',
                'A'
            ],
            'postcode' => '7418ET',
            'firstname' => 'Foo',
            'lastname' => 'Bar',
            'telephone' => '0612345678'
        ];
        /** @var QuoteAddress $billingAddress */
        $billingAddress = $objectManager->create(QuoteAddress::class, ['data' => $billingAddressData]);

        $pickupPointData = [
            LocationInterface::NAME => 'Mobile Express',
            LocationInterface::COUNTRY => 'BE',
            LocationInterface::RETAIL_NETWORK_ID => 'PNPBE-01',
            LocationInterface::LOCATION_CODE => '218791',
            LocationInterface::FROM => '15:00:00',
            LocationInterface::CITY => 'Leuven',
            LocationInterface::HOUSE_NR => '93',
            LocationInterface::STREET => 'Diestsesteenweg',
            LocationInterface::ZIPCODE => '3010'
        ];

        /** @var LocationInterface $postnlPickupLocation */
        $postnlPickupLocation = $objectManager->create(LocationInterface::class, ['data' => $pickupPointData]);

        /** @var RequestBuilder $requestBuilder */
        $requestBuilder = $objectManager->get(RequestBuilder::class);
        $request = $requestBuilder->buildForLocations($billingAddress, $postnlPickupLocation);

        var_dump($request->toArray());
    }
}
