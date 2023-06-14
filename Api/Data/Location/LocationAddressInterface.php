<?php
declare(strict_types=1);

/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Api\Data\Location;

interface LocationAddressInterface
{
    public const CITY = 'City';
    public const COUNTRY_CODE = 'Countrycode';

    public const HOUSE_NR = 'HouseNr';
    public const HOUSE_NR_EXT = 'HouseNrExt';

    public const REMARK = 'Remark';

    public const STREET = 'Street';

    public const ZIPCODE = 'Zipcode';

    public function getCity(): ?string;

    public function getCountryCode(): ?string;

    public function getHouseNr(): ?string;

    public function getHouseNrExt(): ?string;

    public function getRemark(): ?string;

    public function getStreet(): ?string;

    public function getZipcode(): ?string;

    public function getAddressLine1(): string;

    public function getAddressLine2(): string;

    public function toArray(array $keys = []);
}
