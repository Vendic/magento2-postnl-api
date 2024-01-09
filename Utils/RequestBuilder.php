<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Utils;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address;
use Vendic\PostnlApi\Api\Data\LocationInterface;
use Vendic\PostnlApi\Api\Data\PostnlRequestInterface;
use Vendic\PostnlApi\Api\Data\TimeframeInterface;
use Vendic\PostnlApi\Api\Data\PostnlRequestInterfaceFactory;

class RequestBuilder
{
    private PostnlRequestInterfaceFactory $postnlRequestFactory;

    public function __construct(
        PostnlRequestInterfaceFactory $postnlRequestFactory,
    ) {
        $this->postnlRequestFactory = $postnlRequestFactory;
    }

    /**
     * Build the PostNL request for shipment with type pickup
     *
     * Example payload:
     * type: pickup
     * option: PG
     * name: Pakket- en briefautomaat
     * country: NL
     * RetailNetworkID: PNPNL-01
     * LocationCode: 222891
     * from: 15:00:00
     * address[City]: DEVENTER
     * address[Countrycode]: NL
     * address[HouseNr]: 2
     * address[HouseNrExt]: PBA
     * // phpcs:ignore
     * address[Remark]: Dit is een Pakketautomaat met een brievenbus. Hier kunt u pakketten voorzien van een barcodelabel versturen. Pakketten en brieven die u op werkdagen vóór de lichtingstijd afgeeft worden binnen Nederland de volgende dag bezorgd.
     * address[Street]: Hannoverstraat
     * address[Zipcode]: 7418BL
     * customerData[country]: NL
     * customerData[street][]: Keulenstraat
     * customerData[postcode]: 7418ET
     * customerData[housenumber]: 7
     * customerData[firstname]: Test
     * customerData[lastname]: Test
     * customerData[telephone]: 67896789786
     * stated_address_only: 0
     */
    public function buildForPickupSave(Address $billingAddress, LocationInterface $location): PostnlRequestInterface
    {
        /** @var PostnlRequestInterface $request */
        $request = $this->postnlRequestFactory->create();
        $request->setType('pickup');
        $request->setOption('PG');

        // Location address data
        $request->setName($location->getName());
        $request->setAddress($location->getAddress()->toArray());
        $request->setRetailNetworkID($location->getRetailNetworkID());
        $request->setLocationCode($location->getLocationCode());
        $request->setFrom($location->getFrom());
        $request->setCountry($location->getAddress()->getCountryCode());

        // Extract street and housenumber from street array
        /** @var string|array $street */
        $street = $billingAddress->getStreet();

        $streetWithoutNumber = $this->getStreetWithoutHouseNumber($billingAddress);
        $houseNumber = $this->getHousenumber($billingAddress);

        // Customer data
        $request->setCustomerData(
            [
                'country' => $billingAddress->getCountryId(),
                'street' => trim($streetWithoutNumber),
                'postcode' => $billingAddress->getPostcode(),
                'housenumber' => $houseNumber,
                'firstname' => $billingAddress->getFirstname(),
                'lastname' => $billingAddress->getLastname(),
                'telephone' => $billingAddress->getTelephone(),
            ]
        );

        // TODO this should be dynamically if the PostNL settings allow for that.
        $request->setStatedAddressOnly(false);

        return $request;
    }

    /**
     * Build the PostNL request for regular shipment with optional delivery date/timeframe
     *
     * Example payload:
     * address[country]: NL
     * address[street][]: Keulenstraat
     * address[postcode]: 7418ET
     * address[housenumber]: 7
     * address[firstname]: Test
     * address[lastname]: Test
     * address[telephone]: 67896789786
     * type: delivery
     * date: 10-06-2023
     * option: Daytime
     * from: 08:30:00
     * to: 21:30:00
     * country: NL
     * stated_address_only: 0
     */
    public function buildForDeliverySave(
        Address $shippingAddress,
        ?TimeframeInterface $timeframe = null
    ): PostnlRequestInterface {
        /** @var PostnlRequestInterface $request */
        $request = $this->postnlRequestFactory->create();
        $request->setType($timeframe ? 'delivery' : 'fallback');

        // Extract street and housenumber from street array
        /** @var string|array $street */
        $street = $shippingAddress->getStreet();

        $streetWithoutNumber = $this->getStreetWithoutHouseNumber($shippingAddress);
        $houseNumber = $this->getHousenumber($shippingAddress);

        $request->setAddress(
            [
                'country' => $shippingAddress->getCountryId(),
                'street' => [trim($streetWithoutNumber)],
                'postcode' => $shippingAddress->getPostcode(),
                'housenumber' => $houseNumber,
                'firstname' => $shippingAddress->getFirstname(),
                'lastname' => $shippingAddress->getLastname(),
                'telephone' => $shippingAddress->getTelephone(),
            ]
        );

        // TODO, make this dynamic. It only works for NL (and BE ?).
        $request->setStatedAddressOnly(false);
        $request->setCountry($shippingAddress->getCountryId());

        // Set type to EPS for non NL/BE shipments
        if (!in_array($request->getCountry(), ['NL', 'BE'])) {
            $request->setType('EPS');
        }

        // Delivery Timeframes
        if ($timeframe) {
            $request->setDeliveryDate($timeframe->getDate());
            $request->setOption($timeframe->getOption());
            $request->setFrom($timeframe->getFrom());
            $request->setTo($timeframe->getTo());
        }

        return $request;
    }

    /**
     * Build the PostNL request for locations a.k.a. pickup points
     *
     * Example payload:
     * address[country]: BE
     * address[street][]: Keulenstraat
     * address[postcode]: 3010
     * address[housenumber]: 7
     * address[firstname]: Test
     * address[lastname]: Test
     * address[telephone]: 67896789786
     */
    public function buildForLocations(Address $shippingAddress): PostnlRequestInterface
    {
        /** @var PostnlRequestInterface $request */
        $request = $this->postnlRequestFactory->create();

        // Extract street and housenumber from street array
        /** @var string|array $street */
        $street = $shippingAddress->getStreet();

        $streetWithoutNumber = $this->getStreetWithoutHouseNumber($shippingAddress);
        $houseNumber = $this->getHousenumber($shippingAddress);

        // Location address data
        $request->setAddress([
                                 'country' => $shippingAddress->getCountryId(),
                                 'street' => $streetWithoutNumber,
                                 'postcode' => $shippingAddress->getPostcode(),
                                 'housenumber' => $houseNumber,
                                 'firstname' => $shippingAddress->getFirstname(),
                                 'lastname' => $shippingAddress->getLastname(),
                                 'telephone' => $shippingAddress->getTelephone(),
                             ]);
        $request->setCountry($shippingAddress->getCountryId());

        return $request;
    }

    /**
     * Build the PostNL request for timeframes a.k.a. delivery dates
     *
     * Example payload:
     * address[country]: BE
     * address[street][]: Keulenstraat
     * address[postcode]: 3010
     * address[housenumber]: 7
     * address[firstname]: Test
     * address[lastname]: Test
     * address[telephone]: 67896789786
     */
    public function buildforTimeframes(Address $address): PostnlRequestInterface
    {
        // The request for timeframes is identical to locations.
        return $this->buildForLocations($address);
    }

    private function getStreetWithoutHouseNumber(Address $address): string
    {
        $street = $address->getStreet();
        if (!is_array($street)) {
            throw new LocalizedException(__('Street should be an array'));
        }

        // Get the first street, containing the street name
        $firstStreetItem = reset($street);

        // Check if we're dealing with a single line street. If that's the case we'll extract the street using regex.
        if (count($street) === 1) {
            preg_match('/^(?:\b\d\w*\b\s*)?(\b[a-zA-Z]\w*\b\s*)*/', $firstStreetItem, $matches);

            // Get the first street, containing the street name
            return trim($matches[0]);
        }

        // If we're dealing with a multi line street, just return the first line.
        return trim($firstStreetItem);
    }

    private function getHousenumber(Address $address): string
    {
        $street = $address->getStreet();
        if (!$street) {
            throw new LocalizedException(__('Street should be an array'));
        }

        // Trim spaces from all lines
        $trimmedStreet = array_map(
            static function ($line) {
                return trim($line);
            },
            $street
        );

        // Check if we're dealing with a single line street. If that's the case we'll extract the street using regex.
        $streetWithNumberPattern = '/\s(\d+[A-Za-z\s-]*)/';
        $firstStreetItem = reset($street);
        if (count($street) === 1 && preg_match($streetWithNumberPattern, $firstStreetItem, $matches)) {
            // Get the first street, containing the street name
            return trim($matches[0]);
        }

        // Remove first item
        array_shift($trimmedStreet);

        // And return as string
        return trim(implode(' ', $trimmedStreet));
    }
}
