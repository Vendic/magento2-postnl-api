<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Test\Integration\RequestBuilder;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use PHPUnit\Framework\TestCase;
use Vendic\PostnlApi\Api\Data\PostnlRequestInterface;
use Vendic\PostnlApi\Utils\RequestBuilder;

class LocationsRequestTest extends TestCase
{
    public function testForAddressWithAddition(): void
    {
        $quoteAddress = $this->buildQuoteAddress(
            [
                'street' => "Nederlandsestraat\n15\nB",
                'postcode' => '1234AB',
                'city' => 'Deventer',
                'country_id' => 'NL'
            ]
        );

        $request = $this->buildRequestForQuoteAddress($quoteAddress);

        $this->assertEquals('NL', $request->getAddress()['country']);
        $this->assertEquals('Nederlandsestraat', $request->getAddress()['street']);
        $this->assertEquals('1234AB', $request->getAddress()['postcode']);
        $this->assertEquals('15 B', $request->getAddress()['housenumber']);
    }

    public function testForAddressWithoutAddition(): void
    {
        $quoteAddress = $this->buildQuoteAddress(
            [
                'street' => "Nederlandsestraat\n15",
                'postcode' => '1234AB',
                'city' => 'Deventer',
                'country_id' => 'NL'
            ]
        );

        $request = $this->buildRequestForQuoteAddress($quoteAddress);

        $this->assertEquals('NL', $request->getAddress()['country']);
        $this->assertEquals('Nederlandsestraat', $request->getAddress()['street']);
        $this->assertEquals('1234AB', $request->getAddress()['postcode']);
        $this->assertEquals('15', $request->getAddress()['housenumber']);
    }

    public function testWithStreetArray(): void
    {
        $quoteAddress = $this->buildQuoteAddress(
            [
                'street' => [
                    'Nederlandsestraat',
                    '15',
                    'B'
                ],
                'postcode' => '1234AB',
                'city' => 'Deventer',
                'country_id' => 'NL'
            ]
        );

        $request = $this->buildRequestForQuoteAddress($quoteAddress);

        $this->assertEquals('NL', $request->getAddress()['country']);
        $this->assertEquals('Nederlandsestraat', $request->getAddress()['street']);
        $this->assertEquals('1234AB', $request->getAddress()['postcode']);
        $this->assertEquals('15 B', $request->getAddress()['housenumber']);
    }

    public function testWithSingleLineStreet() : void
    {
        $quoteAddress = $this->buildQuoteAddress(
            [
                'street' => '1e Nederlandsestraat 15 B',
                'postcode' => '1234AB',
                'city' => 'Deventer',
                'country_id' => 'NL'
            ]
        );

        $request = $this->buildRequestForQuoteAddress($quoteAddress);

        $this->assertEquals('NL', $request->getAddress()['country']);
        $this->assertEquals('1e Nederlandsestraat', $request->getAddress()['street']);
        $this->assertEquals('1234AB', $request->getAddress()['postcode']);
        $this->assertEquals('15 B', $request->getAddress()['housenumber']);
    }

    private function buildQuoteAddress(array $data): QuoteAddress
    {
        return Bootstrap::getObjectManager()->create(QuoteAddress::class, ['data' => $data]);
    }

    private function buildRequestForQuoteAddress(QuoteAddress $quoteAddress): PostnlRequestInterface
    {
        $objectManager = Bootstrap::getObjectManager();

        $requestBuilder = $objectManager->get(RequestBuilder::class);
        return $requestBuilder->buildForLocations($quoteAddress);
    }

}
