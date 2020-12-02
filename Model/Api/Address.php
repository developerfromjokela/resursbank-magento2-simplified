<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Model\Api;

/**
 * Represents customer address information that was fetched from the server.
 * This information is incompatible with Magento's address standard and has to
 * be converted before use, because fields do not have the same names as their
 * Magento counterpart, or some fields that exists in Magento are absent in the
 * API data.
 */
class Address
{
    /**
     * Whether this address was fetched for a person or a company customer.
     *
     * NOTE: Not part of the address information returned from the API.
     *
     * NOTE: It's not clear from the data returned by the API what kind of
     * customer the address belongs to. By specifying it here we don't have to
     * pass around a flag stating its ownership.
     *
     * @var bool
     */
    private $isCompany;

    /**
     * What the full name represents depends on the customer type. If the
     * customer is a person, it is the full name of the customer. If the
     * customer is a company, it is the name of the company.
     *
     * @var string
     */
    private $fullName;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $addressRow1;

    /**
     * @var string
     */
    private $addressRow2;

    /**
     * This would be the city of a customer.
     *
     * @var string
     */
    private $postalArea;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var string
     */
    private $country;

    /**
     * @param bool $isCompany
     * @param string $fullName
     * @param string $firstName
     * @param string $lastName
     * @param string $addressRow1
     * @param string $addressRow2
     * @param string $postalArea
     * @param string $postalCode
     * @param string $country
     */
    public function __construct(
        bool $isCompany = false,
        string $fullName = '',
        string $firstName = '',
        string $lastName = '',
        string $addressRow1 = '',
        string $addressRow2 = '',
        string $postalArea = '',
        string $postalCode = '',
        string $country = ''
    ) {
        $this->isCompany = $isCompany;
        $this->fullName = $fullName;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->addressRow1 = $addressRow1;
        $this->addressRow2 = $addressRow2;
        $this->postalArea = $postalArea;
        $this->postalCode = $postalCode;
        $this->country = $country;
    }

    /**
     * @see Address::$isCompany
     * @param bool $value
     * @return self
     */
    public function setIsCompany(
        bool $value
    ): self {
        $this->isCompany = $value;

        return $this;
    }

    /**
     * @see Address::$isCompany
     * @return string
     */
    public function getIsCompany(): string
    {
        return $this->fullName;
    }

    /**
     * @see Address::$fullName
     * @param string $value
     * @return self
     */
    public function setFullName(
        string $value
    ): self {
        $this->fullName = $value;

        return $this;
    }

    /**
     * @see Address::$fullName
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setFirstName(
        string $value
    ): self {
        $this->firstName = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setLastName(
        string $value
    ): self {
        $this->lastName = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setAddressRow1(
        string $value
    ): self {
        $this->addressRow1 = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressRow1(): string
    {
        return $this->addressRow1;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setAddressRow2(
        string $value
    ): self {
        $this->addressRow2 = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressRow2(): string
    {
        return $this->addressRow2;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setPostalArea(
        string $value
    ): self {
        $this->postalArea = $value;

        return $this;
    }

    /**
     * @see Address::$postalArea
     * @return string
     */
    public function getPostalArea(): string
    {
        return $this->postalArea;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setPostalCode(
        string $value
    ): self {
        $this->postalCode = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setCountry(
        string $value
    ): self {
        $this->country = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }
}
