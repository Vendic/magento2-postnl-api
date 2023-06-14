<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Utils;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\EncoderInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use TIG\PostNL\Model\OrderRepository;
use TIG\PostNL\Service\Carrier\QuoteToRateRequest;
use TIG\PostNL\Service\Quote\ShippingDuration;
use TIG\PostNL\Webservices\Endpoints\DeliveryDate;

class DeliveryOptionsUtils
{
    private OrderRepository $orderRepository;
    private Session $checkoutSession;
    private QuoteToRateRequest $quoteToRateRequest;
    private ShippingDuration $shippingDuration;
    private DeliveryDate $deliveryEndpoint;

    public function __construct(
        OrderRepository $orderRepository,
        Session $checkoutSession,
        QuoteToRateRequest $quoteToRateRequest,
        ShippingDuration $shippingDuration,
        DeliveryDate $deliveryDate
    ) {
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
        $this->quoteToRateRequest = $quoteToRateRequest;
        $this->shippingDuration = $shippingDuration;
        $this->deliveryEndpoint = $deliveryDate;
    }

    /**
     * @param string|int $quoteId
     */
    public function getPostNLOrderByQuoteId($quoteId): \TIG\PostNL\Model\Order
    {
        /** @var \TIG\PostNL\Model\Order|null $postnlOrder */
        // Ignoring next line since the type hint in the postnl order repo is wrong.
        // @phpstan-ignore-next-line
        $postnlOrder = $this->orderRepository->getByQuoteId($quoteId);
        if (!$postnlOrder) {
            return $this->orderRepository->create();
        }

        if ($postnlOrder->getOrderId()) {
            // double quote, order probably canceled before. so add new record.
            return $this->orderRepository->create();
        }

        return $postnlOrder;
    }

    /**
     * @returns array|mixed
     * CIF call to get the delivery day needed for the StartDate param in TimeFrames Call.
     */
    public function getDeliveryDay(array $address): string
    {
        $quote = $this->checkoutSession->getQuote();
        $storeId = $quote->getStoreId();
        $shippingDuration = $this->shippingDuration->get();
        $this->deliveryEndpoint->updateApiKey($storeId);
        $this->deliveryEndpoint->updateParameters($address, $shippingDuration);
        $response = $this->deliveryEndpoint->call();

        if (!is_object($response) || !isset($response->DeliveryDate)) {
            return __('Invalid GetDeliveryDate response: %1', var_export($response, true))->render();
        }

        $this->checkoutSession->setPostNLDeliveryDate($response->DeliveryDate);
        return $response->DeliveryDate;
    }

    public function getRateRequest(array $address): RateRequest
    {
        /** @var RateRequest $request */
        $request = $this->quoteToRateRequest->get();
        $request->setDestCountryId($address['country']);
        $request->setDestPostcode($address['postcode']);

        $shippingAddress = $request->getShippingAddress();
        $shippingAddress->setCountryId($address['country']);
        $shippingAddress->setPostcode($address['postcode']);
        $request->setShippingAddress($shippingAddress);

        return $request;
    }
}
