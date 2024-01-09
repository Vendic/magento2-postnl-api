<?php
declare(strict_types=1);

/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Api\Data;

interface PostnlRequestInterface
{
    public function getParams(): array;

    public function getAddress(): ?array;

    /**
     * @param array $address {
     *  country: string,
     *  street: string[],
     *  postcode: string,
     *  housenumber: string,
     *  firstname: string,
     *  lastname: string
     *  telephone: string
     * }
     * @return void
     */
    public function setAddress(array $address): void;

    public function setType(string $type): void;

    public function getType(): string;

    public function setOption(string $option): void;

    public function setCountry(string $countryId): void;

    public function getCountry(): string;

    /**
     * @return null|int|string
     */
    public function getQuoteId();

    public function setStatedAddressOnly(bool $statedAddressOnly): void;

    public function getStatedAddressOnly(): bool;

    public function setCustomerData(array $array);

    public function setRetailNetworkID($retailNetworkId);

    public function setLocationCode($locationCode);

    public function setName($name);

    public function setFrom($from);

    public function setDeliveryDate($deliveryDate);

    public function setTo($to);
}
