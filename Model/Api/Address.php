<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Model\Api;

use Resursbank\Core\Exception\ApiDataException;
use function strlen;

/**
 * This class is used to structure address information fetched through ECom.
 * Using this we can always be sure the properties we require exist and have the
 * correct type.
 */
class Address
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
     * @var string
     */
    private $postcode;

    /**
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
     * @param string $val
     * @return $this
     */
    public function setPostcode(
        string $val
    ): self {
        // Magento expects postcodes to be formatted as "123 45".
        if (strlen($val) > 3) {
            $this->postcode = substr($val, 0, 3) . ' ' . substr($val, 3);
        } else {
            $this->postcode = $val;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPostcode(): string
    {
        return $this->postcode;
    }

    /**
     * @param string $val
     * @return $this
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
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Creates and returns an array of the address data.
     *
     * @return array
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
