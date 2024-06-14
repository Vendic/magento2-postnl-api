<?php declare(strict_types=1);

namespace Vendic\PostnlApi\Service\DeliveryOptions;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use TIG\PostNL\Config\Provider\AddressConfiguration;
use TIG\PostNL\Config\Provider\ProductOptions;
use TIG\PostNL\Helper\DeliveryOptions\OrderParams;
use TIG\PostNL\Helper\DeliveryOptions\PickupAddress;
use TIG\PostNL\Model\Order as PostnlOrder;
use TIG\PostNL\Model\OrderRepository as TigOrderRepository;
use Vendic\PostnlApi\Api\Data\PostnlRequestInterface;
use Vendic\PostnlApi\Utils\DeliveryOptionsUtils as Utils;
use Vendic\PostnlApi\Api\SaveInterface;

class Save implements SaveInterface
{
    private OrderParams $orderParams;

    private PickupAddress $pickupAddress;

    private ProductOptions $productOptions;
    private AddressConfiguration $addressConfiguration;
    private Utils $utils;
    private CheckoutSession $checkoutSession;
    private TigOrderRepository $orderRepository;

    public function __construct(
        Utils $utils,
        OrderParams $orderParams,
        PickupAddress $pickupAddress,
        ProductOptions $productOptions,
        AddressConfiguration $addressConfiguration,
        CheckoutSession $checkoutSession,
        TigOrderRepository $orderRepository
    ) {
        $this->orderParams = $orderParams;
        $this->pickupAddress = $pickupAddress;
        $this->productOptions = $productOptions;
        $this->addressConfiguration = $addressConfiguration;
        $this->utils = $utils;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
    }

    public function execute(PostnlRequestInterface $postnlRequest): bool
    {
        if (!$postnlRequest->getType()) {
            throw new LocalizedException(__('No Type specified'));
        }

        if (!$postnlRequest->getCountry()) {
            throw new LocalizedException(__('No Country specified'));
        }

        return $this->saveDeliveryOption($postnlRequest);
    }

    private function saveDeliveryOption(PostnlRequestInterface $postnlRequest): bool
    {
        $type = $postnlRequest->getType();
        $params = $this->orderParams->get($this->addSessionDataToParams($postnlRequest->getParams()));
        $postnlOrder = $this->utils->getPostNLOrderByQuoteId($params['quote_id']);

        $this->savePostNLOrderData($params, $postnlOrder);

        if ($type == 'pickup') {
            $this->pickupAddress->set($params['pg_address']);
        } else if ($type == 'delivery') {
            $this->pickupAddress->remove();
        }

        return true;
    }

    private function savePostNLOrderData(array $params, PostnlOrder $postnlOrder): void
    {
        foreach ($params as $key => $value) {
            if ($key === PostnlOrder::FIELD_AC_INFORMATION) {
                $value = $this->convertParamValueToJson($value);
            }

            if (empty($value)) {
                continue;
            }

            $postnlOrder->setData($key, $value);
        }

        $country = $params['country'];
        $shopCountry = $this->addressConfiguration->getCountry();
        $postnlOrder->setIsStatedAddressOnly(false);
        if (isset($params['stated_address_only']) && $params['stated_address_only']) {
            $postnlOrder->setIsStatedAddressOnly(true);
            $postnlOrder->setProductCode(
                $this->productOptions->getDefaultStatedAddressOnlyProductOption($country, $shopCountry)
            );
        }

        $this->orderRepository->save($postnlOrder);
    }

    private function addSessionDataToParams(array $params) : array
    {
        //If no delivery date and the type is pickup, fallback, EPS or GP then retrieve the PostNL delivery date
        if (!isset($params['date']) &&
            ($params['type'] === 'pickup' || $params['type'] === 'fallback'
                || $params['type'] === 'EPS' || $params['type'] === 'GP'
                || $params['type'] === 'Letterbox Package')
        ) {
            $params['date'] = $this->checkoutSession->getPostNLDeliveryDate();
        }

        $params['quote_id'] = $this->checkoutSession->getQuoteId();

        // Recalculate the delivery date if it's unknown for pickup
        if (!isset($params['date']) && $params['type'] == 'pickup') {
            $params['address']['country'] = $params['address']['Countrycode'];
            $params['address']['postcode'] = $params['address']['Zipcode'];
            $params['date'] = $this->utils->getDeliveryDay($params['address']);
        }

        if ($params['date'] === 'keep') {
            unset($params['date']);
        }

        return $params;
    }

    public function convertParamValueToJson(mixed $value) : mixed
    {
        if (is_array($value) && empty($value)) {
            $value = null;
        }

        if (!empty($value)) {
            $value = json_encode($value);
        }
        return $value;
    }
}
