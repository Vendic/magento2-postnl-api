<?php declare(strict_types=1);

namespace Vendic\PostnlApi\Service\DeliveryOptions;

use Magento\Framework\Serialize\SerializerInterface;
use TIG\PostNL\Helper\AddressEnhancer;
use TIG\PostNL\Service\Carrier\Price\Calculator;
use TIG\PostNL\Webservices\Endpoints\Locations as LocationsEndpoint;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Vendic\PostnlApi\Api\Data\LocationInterface;
use Vendic\PostnlApi\Api\Data\LocationInterfaceFactory;
use Vendic\PostnlApi\Api\Data\PostnlRequestInterface;
use Vendic\PostnlApi\Api\LocationsInterface;
use Vendic\PostnlApi\Utils\DeliveryOptionsUtils as Utils;

class Locations implements LocationsInterface
{
    private AddressEnhancer $addressEnhancer;
    private LocationsEndpoint $locationsEndpoint;
    private Calculator $priceCalculator;
    private Utils $utils;
    private Session $checkoutSession;
    private SerializerInterface $serializer;
    private LocationInterfaceFactory $locationFactory;

    public function __construct(
        LocationInterfaceFactory $locationFactory,
        SerializerInterface $serializer,
        Session $checkoutSession,
        Utils $utils,
        AddressEnhancer $addressEnhancer,
        LocationsEndpoint $locations,
        Calculator $priceCalculator,
    ) {
        $this->addressEnhancer   = $addressEnhancer;
        $this->locationsEndpoint = $locations;
        $this->priceCalculator   = $priceCalculator;
        $this->utils = $utils;
        $this->checkoutSession = $checkoutSession;
        $this->serializer = $serializer;
        $this->locationFactory = $locationFactory;
    }

    public function get(PostnlRequestInterface $postnlRequest) : array
    {
        $address = $postnlRequest->getAddress();

        if (!isset($address) || !is_array($address)) {
            throw new LocalizedException(__('No Address data found.'));
        }
        $this->addressEnhancer->set($address);
        $price = $this->priceCalculator->getPriceWithTax(
            $this->utils->getRateRequest($address),
            'pakjegemak'
        );

        return [
            'price'       => $price['price'],
            'locations'   => $this->getValidResponeType(),
            'pickup_date' => $this->utils->getDeliveryDay($this->addressEnhancer->get())
        ];
    }

    /**
     * @param $address
     *
     * @return LocationInterface[]
     * @throws \Exception
     */
    private function getLocations($address)
    {
        $deliveryDate = false;
        if ($this->utils->getDeliveryDay($address)) {
            $deliveryDate = $this->utils->getDeliveryDay($address);
        }

        $quote = $this->checkoutSession->getQuote();
        $storeId = $quote->getStoreId();
        $this->locationsEndpoint->changeAPIKeyByStoreId($storeId);
        $this->locationsEndpoint->updateParameters($address ,$deliveryDate);
        $response = $this->locationsEndpoint->call();
        //@codingStandardsIgnoreLine
        if (!is_object($response) || !isset($response->GetLocationsResult->ResponseLocation)) {
            throw new LocalizedException(
                __('Invalid GetLocationsResult response: %1', var_export($response, true))
            );
        }

        //@codingStandardsIgnoreLine
        $response = $response->GetLocationsResult->ResponseLocation;
        $responseArray = $this->serializer->unserialize($this->serializer->serialize($response));
        $factory = $this->locationFactory;

        return array_map(
            static  function (array $locationData) use ($factory) {
                return $factory->create(['data' => $locationData]);
            },
            $responseArray
        );
    }

    /**
     * @return array|\Magento\Framework\Phrase
     */
    private function getValidResponeType()
    {
        $address = $this->addressEnhancer->get();

        if (isset($address['error'])) {
            //@codingStandardsIgnoreLine
            return ['error' => __('%1 : %2', $address['error']['code'], $address['error']['message'])];
        }

        return $this->getLocations($address);
    }
}
