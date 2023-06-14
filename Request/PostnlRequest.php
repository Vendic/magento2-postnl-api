<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Request;

use Magento\Framework\DataObject;
use Vendic\PostnlApi\Api\Data\PostnlRequestInterface;

/**
 * Request class that's used for all delivery options endpoints
 */
class PostnlRequest extends DataObject implements PostnlRequestInterface
{
    public function getParams(): array
    {
        return $this->getData();
    }

    public function getAddress(): ?array
    {
        return $this->getData('address');
    }

    public function setAddress(array $address): void
    {
        $this->setData('address', $address);
    }

    public function getType(): string
    {
        return $this->getData('type');
    }

    public function setType(string $type): void
    {
        $this->setData('type', $type);
    }

    public function getCountry(): string
    {
        return $this->getData('country');
    }

    public function setCountry(string $countryId): void
    {
        $this->setData('country', $countryId);
    }

    /**
     * @return string|int
     */
    public function getQuoteId()
    {
        return $this->getData('quote_id');
    }

    public function setStatedAddressOnly(bool $statedAddressOnly): void
    {
        $this->setData('stated_address_only', (int)$statedAddressOnly);
    }

    public function getStatedAddressOnly(): bool
    {
        return (bool)$this->getData('stated_address_only');
    }

    public function setCustomerData(array $array): void
    {
        $this->setData('customerData', $array);
    }

    public function setRetailNetworkID($retailNetworkId): void
    {
        $this->setData('RetailNetworkID', $retailNetworkId);
    }

    public function setLocationCode($locationCode): void
    {
        $this->setData('LocationCode', $locationCode);
    }

    public function setOption(string $option): void
    {
        $this->setData('option', $option);
    }

    public function setName($name): void
    {
        $this->setData('name', $name);
    }

    public function setFrom($from): void
    {
        $this->setData('from', $from);
    }
}
