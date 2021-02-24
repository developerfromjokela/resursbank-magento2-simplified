<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Model;

use Resursbank\Core\Exception\ApiDataException;
use function strlen;

/**
 * This class is meant to represent a valid address object on the checkout page.
 * It is not meant to replace any of Magento's own address implementations, but
 * rather to help us out when we need to convert address information fetched
 * from the API.
 *
 * The address information that comes from the API is not directly compatible
 * with Magento's checkout process (and neither should we expect it to be) as
 * it may not contain the right number of fields, or the fields have names that
 * differ from Magento's own.
 */
class CheckoutAddress
{
    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $city;

    /**
     * Expected to be formatted like: "123 45".
     *
     * @var string
     */
    private $postcode;

    /**
     * Only valid countries are allowed.
     *
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    public $street0;

    /**
     * @var string
     */
    public $street1;

    /**
     * @var string
     */
    public $company;

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $city
     * @param string $postcode
     * @param string $country
     * @param string $street0
     * @param string $street1
     * @param string $company
     * @throws ApiDataException
     */
    public function __construct(
        string $firstName,
        string $lastName,
        string $city,
        string $postcode,
        string $country,
        string $street0,
        string $street1 = '',
        string $company = ''
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->city = $city;
        $this->street0 = $street0;
        $this->street1 = $street1;
        $this->company = $company;

        $this->setPostcode($postcode)
            ->setCountry($country);
    }

    /**
     * @see CheckoutAddress::$postcode
     * @param string $val
     * @return self
     */
    public function setPostcode(
        string $val
    ): self {
        // Magento expects postcodes to be formatted as "123 45".
        if (strlen($val) > 3) {
            $this->postcode =
                substr($val, 0, 3) .
                ' ' .
                substr($val, 3);
        } else {
            $this->postcode = $val;
        }

        return $this;
    }

    /**
     * @see CheckoutAddress::$postcode
     * @return string
     */
    public function getPostcode(): string
    {
        return $this->postcode;
    }

    /**
     * @see CheckoutAddress::$country
     * @param string $val
     * @return self
     * @throws ApiDataException
     */
    public function setCountry(
        string $val
    ): self {
        if ($val !== 'SE') {
            throw new ApiDataException(
                __('%1 is not a valid country.', $val)
            );
        }

        $this->country = $val;

        return $this;
    }

    /**
     * @see CheckoutAddress::$country
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'firstname' => $this->firstName,
            'lastname' => $this->lastName,
            'city' => $this->city,
            'postcode' => $this->postcode,
            'country' => $this->country,
            'street0' => $this->street0,
            'street1' => $this->street1,
            'company' => $this->company,
        ];
    }
}
