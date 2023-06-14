<?php
declare(strict_types=1);

/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Api\Data;

use Vendic\PostnlApi\Api\Data\Location\LocationAddressInterface;

interface LocationInterface
{
    public const NAME = 'Name';
    public const COUNTRY = 'Country';
    public const FROM = 'from';
    public const RETAIL_NETWORK_ID = 'RetailNetworkID';
    public const LOCATION_CODE = 'LocationCode';
    public const DISTANCE = 'Distance';
    public const ADDRESS = 'Address';
    public const OPENING_HOURS = 'OpeningHours';

    public function getName(): ?string;

    public function getCountry(): ?string;

    public function getFrom(): ?string;

    public function getRetailNetworkID(): ?string;

    public function getLocationCode(): ?string;

    public function getDistance(): ?string;

    public function getAddress(): LocationAddressInterface;

    public function getAddressData(): array;

    public function getOpeningHours(): array;
}
