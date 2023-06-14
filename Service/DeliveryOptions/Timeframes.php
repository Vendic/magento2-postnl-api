<?php declare(strict_types=1);

namespace Vendic\PostnlApi\Service\DeliveryOptions;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use TIG\PostNL\Config\CheckoutConfiguration\IsDeliverDaysActive;
use TIG\PostNL\Controller\AbstractDeliveryOptions;
use TIG\PostNL\Helper\AddressEnhancer;
use TIG\PostNL\Model\OrderRepository;
use TIG\PostNL\Service\Carrier\Price\Calculator;
use TIG\PostNL\Service\Carrier\QuoteToRateRequest;
use TIG\PostNL\Service\Converter\CanaryIslandToIC;
use TIG\PostNL\Service\Quote\ShippingDuration;
use TIG\PostNL\Service\Shipment\EpsCountries;
use TIG\PostNL\Service\Shipping\LetterboxPackage;
use TIG\PostNL\Service\Validation\CountryShipping;
use TIG\PostNL\Webservices\Endpoints\DeliveryDate;
use TIG\PostNL\Webservices\Endpoints\TimeFrame;
use Vendic\PostnlApi\Api\Data\PostnlRequestInterface;
use Vendic\PostnlApi\Api\TimeframesInterface;
use Vendic\PostnlApi\Utils\DeliveryOptionsUtils as Utils;

class Timeframes implements TimeframesInterface
{
    private AddressEnhancer $addressEnhancer;

    private TimeFrame $timeFrameEndpoint;

    private Calculator $calculator;

    private IsDeliverDaysActive $isDeliveryDaysActive;

    private LetterboxPackage $letterboxPackage;

    private CanaryIslandToIC $canaryConverter;

    private CountryShipping $countryShipping;
    private Utils $utils;
    private CheckoutSession $checkoutSession;

    public function __construct(
        Utils $utils,
        CheckoutSession $checkoutSession,
        AddressEnhancer $addressEnhancer,
        TimeFrame $timeFrame,
        Calculator $calculator,
        IsDeliverDaysActive $isDeliverDaysActive,
        LetterboxPackage $letterboxPackage,
        CanaryIslandToIC $canaryConverter,
        CountryShipping $countryShipping
    ) {
        $this->addressEnhancer = $addressEnhancer;
        $this->timeFrameEndpoint = $timeFrame;
        $this->calculator = $calculator;
        $this->isDeliveryDaysActive = $isDeliverDaysActive;
        $this->letterboxPackage = $letterboxPackage;
        $this->canaryConverter = $canaryConverter;
        $this->countryShipping = $countryShipping;
        $this->utils = $utils;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param PostnlRequestInterface $postnlRequest
     * @return array|false
     * @throws \TIG\PostNL\Exception
     */
    public function get(PostnlRequestInterface $postnlRequest)
    {
        $params = $postnlRequest->getParams();
        $address = $postnlRequest->getAddress();

        if (!$address || !is_array($address)) {
            return $this->getFallBackResponse(1);
        }

        $address = $postnlRequest->getAddress() ?? [];
        $price = $this->calculator->getPriceWithTax($this->utils->getRateRequest($address));

        if (!isset($price['price'])) {
            return false;
        }

        $quote = $this->checkoutSession->getQuote();
        $cartItems = $quote->getAllItems();

        if ($this->letterboxPackage->isLetterboxPackage($cartItems, false) && $address['country'] == 'NL') {
            return $this->getLetterboxPackageResponse($price['price']);
        }

        // Ignoring invalid parameter #1, since isCanaryIsland accepts boths arrays and Address instances.
        // @phpstan-ignore-next-line
        if (in_array($address['country'], EpsCountries::ALL) && $address['country'] === 'ES'
            && $this->canaryConverter->isCanaryIsland($address)) {
            return $this->getGlobalPackResponse($price['price']);
        }

        if ($this->isEpsCountry($params)) {
            return $this->getEpsCountryResponse($price['price']);
        }

        if (!in_array($address['country'], EpsCountries::ALL) && !in_array($address['country'], ['BE', 'NL'])) {
            return $this->getGlobalPackResponse($price['price']);
        }

        if (!$this->isDeliveryDaysActive->getValue()) {
            return $this->getFallBackResponse(2, $price['price']);
        }

        $this->addressEnhancer->set($address);

        try {
            return $this->getValidResponseType($price['price']);
        } catch (\Exception $exception) {
            return $this->getFallBackResponse(3, $price['price']);
        }
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    private function isEpsCountry($params)
    {
        if (!in_array($params['address']['country'], EpsCountries::ALL)) {
            return false;
        }

        // NL to BE/NL shipments are not EPS shipments
        if ($this->countryShipping->isShippingNLToEps($params['address']['country'])) {
            return true;
        }

        // BE to BE shipments is not EPS, but BE to NL is
        if ($this->countryShipping->isShippingBEToEps($params['address']['country'])) {
            return true;
        }

        return false;
    }

    /**
     * @param $address
     *
     * @return array|\Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Webapi\Exception
     * @throws \TIG\PostNL\Webservices\Api\Exception
     */
    private function getPossibleDeliveryDays(array $address)
    {
        $startDate = $this->utils->getDeliveryDay($address);

        return $this->getTimeFrames($address, $startDate);
    }

    /**
     * @param int|float|string $price
     */
    private function getValidResponseType($price) : array
    {
        $address = $this->addressEnhancer->get();

        if (isset($address['error'])) {
            return [
                'error' => __('%1 : %2', $address['error']['code'], $address['error']['message'])->render(),
                'price' => $price,
                'timeframes' => [[['fallback' => __('At the first opportunity')->render()]]]
            ];
        }

        $timeframes = $this->getPossibleDeliveryDays($address);
        if (empty($timeframes)) {
            return [
                'error' => __('No timeframes available.'),
                'price' => $price,
                'timeframes' => [[['fallback' => __('At the first opportunity')]]]
            ];
        }

        return [
            'price' => $price,
            'timeframes' => $timeframes
        ];
    }

    /**
     * CIF call to get the timeframes.
     *
     * @param $address
     * @param $startDate
     *
     * @return array|\Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Webapi\Exception
     * @throws \TIG\PostNL\Webservices\Api\Exception
     */
    private function getTimeFrames($address, $startDate)
    {
        $quote = $this->checkoutSession->getQuote();
        $storeId = $quote->getStoreId();
        $this->timeFrameEndpoint->updateApiKey($storeId);
        $this->timeFrameEndpoint->fillParameters($address, $startDate);

        return $this->timeFrameEndpoint->call();
    }

    /**
     * @param $error
     * @param $price
     * @return array
     */
    private function getFallBackResponse($error = 0, $price = null): array
    {
        $errorMessage = isset($this->returnErrors[$error]) ? $this->returnErrors[$error] : '';
        return [
            'error' => __($errorMessage),
            'price' => $price,
            'timeframes' => [[['fallback' => __('At the first opportunity')]]]
        ];
    }

    private function getLetterboxPackageResponse($price): array
    {
        return [
            'price' => $price,
            'letterbox_package' => true,
            'timeframes' => [
                [
                    [
                        'letterbox_package' => __(
                            'Your order is a letterbox package and will be '
                            . 'delivered from Tuesday to Saturday.'
                        )
                    ]
                ]
            ]
        ];
    }

    /**
     * @param $price
     *
     * @return array
     */
    private function getEpsCountryResponse($price): array
    {
        return [
            'price' => $price,
            'timeframes' => [[['eps' => __('Ship internationally')]]]
        ];
    }

    /**
     * @param $price
     *
     * @return array
     */
    private function getGlobalPackResponse($price): array
    {
        return [
            'price' => $price,
            'timeframes' => [[['gp' => __('Ship internationally')]]]
        ];
    }
}
