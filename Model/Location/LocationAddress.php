<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Model\Location;

use Vendic\PostnlApi\Api\Data\Location\LocationAddressInterface;
use Magento\Framework\DataObject;

class LocationAddress extends DataObject implements LocationAddressInterface
{
    public function getCity(): ?string
    {
        return $this->getData(LocationAddressInterface::CITY);
    }

    public function getCountryCode(): ?string
    {
        return $this->getData(LocationAddressInterface::COUNTRY_CODE);
    }

    public function getHouseNr(): ?string
    {
        return $this->getData(LocationAddressInterface::HOUSE_NR);
    }

    public function getHouseNrExt(): ?string
    {
        return $this->getData(LocationAddressInterface::HOUSE_NR_EXT);
    }

    public function getRemark(): ?string
    {
        return $this->getData(LocationAddressInterface::REMARK);
    }

    public function getStreet(): ?string
    {
        return $this->getData(LocationAddressInterface::STREET);
    }

    public function getZipcode(): ?string
    {
        return $this->getData(LocationAddressInterface::ZIPCODE);
    }

    public function getAddressLine1(): string
    {
        return trim(
            sprintf(
                '%s %s %s',
                $this->getStreet(),
                $this->getHouseNr(),
                $this->getHouseNrExt()
            )
        );
    }

    public function getAddressLine2(): string
    {
        return trim(
            sprintf(
                '%s %s',
                $this->getZipcode(),
                $this->getCity(),
            )
        );
    }
}
