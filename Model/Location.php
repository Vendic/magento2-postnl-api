<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Model;

use Vendic\PostnlApi\Api\Data\Location\LocationAddressInterface;
use Vendic\PostnlApi\Api\Data\Location\LocationAddressInterfaceFactory;
use Vendic\PostnlApi\Api\Data\LocationInterface;
use Magento\Framework\DataObject;

class Location extends DataObject implements LocationInterface
{
    private LocationAddressInterfaceFactory $locationAddressFactory;

    public function __construct(
        LocationAddressInterfaceFactory $locationAddressFactory,
        array $data = []
    ) {
        $this->locationAddressFactory = $locationAddressFactory;
        parent::__construct($data);
    }

    public function getName(): ?string
    {
        return $this->getData(LocationInterface::NAME);
    }

    public function getCountry(): ?string
    {
        return $this->getData(LocationInterface::COUNTRY);
    }

    public function getFrom(): ?string
    {
        return $this->getData(LocationInterface::FROM);
    }

    public function getRetailNetworkID(): ?string
    {
        return $this->getData(LocationInterface::RETAIL_NETWORK_ID);
    }

    public function getLocationCode(): ?string
    {
        return $this->getData(LocationInterface::LOCATION_CODE);
    }

    public function getDistance(): ?string
    {
        return $this->getData(LocationInterface::DISTANCE);
    }

    public function getAddress(): LocationAddressInterface
    {
        return $this->locationAddressFactory->create(['data' => $this->getAddressData()]);
    }

    public function getAddressData(): array
    {
        return $this->getData(LocationInterface::ADDRESS);
    }

    public function getOpeningHours(): array
    {
        return $this->getData(LocationInterface::OPENING_HOURS);
    }
}
