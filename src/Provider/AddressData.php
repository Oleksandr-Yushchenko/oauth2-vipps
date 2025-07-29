<?php

namespace League\OAuth2\Client\Provider;

/**
 * Class Address Data.
 */
class AddressData
{

    /**
     * Address type.
     *
     * @var string|null
     */
    private $addressType;

    /**
     * Country.
     *
     * @var string|null
     */
    private $country;

    /**
     * Formatted.
     *
     * @var string|null
     */
    private $formatted;

    /**
     * Postal code.
     *
     * @var string|null
     */
    private $postalCode;

    /**
     * Region.
     *
     * @var string|null
     */
    private $region;

    /**
     * Street.
     *
     * @var string|null
     */
    private $streetAddress;

    /**
     * AddressData constructor.
     *
     * @param string|null $address_type
     *   Address type.
     * @param string|null $country
     *   Country.
     * @param string|null $formatted
     *   Formatted.
     * @param string|null $postalCode
     *   Postal code.
     * @param string|null $region
     *   Region.
     * @param string|null $streetAddress
     *   Street.
     */
    public function __construct(
        ?string $address_type,
        ?string $country,
        ?string $formatted,
        ?string $postalCode,
        ?string $region,
        ?string $streetAddress
    ) {
        $this->addressType = $address_type;
        $this->country = $country;
        $this->formatted = $formatted;
        $this->postalCode = $postalCode;
        $this->region = $region;
        $this->streetAddress = $streetAddress;
    }

    /**
     * Get address type.
     *
     * @return string|null
     *   Address type.
     */
    public function getAddressType(): ?string
    {
        return $this->addressType;
    }

    /**
     * Get country.
     *
     * @return string|null
     *   Country.
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Get formatted.
     *
     * @return string|null
     *   Formatted.
     */
    public function getFormatted(): ?string
    {
        return $this->formatted;
    }

    /**
     * Get address postal code.
     *
     * @return string|null
     *   Postal code.
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * Get region address.
     *
     * @return string|null
     *   Region.
     */
    public function getRegion(): ?string
    {
        return $this->region;
    }

    /**
     * Get street address.
     *
     * @return string|null
     *   Street address.
     */
    public function getStreetAddress(): ?string
    {
        return $this->streetAddress;
    }

}
