<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\ValidatorException;
use Resursbank\Core\Exception\ApiDataException;
use Resursbank\Core\Helper\Api as CoreApi;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Simplified\Model\Api\Address as ApiAddress;
use Resursbank\Simplified\Model\CheckoutAddress;
use stdClass;

class Address extends AbstractHelper
{
    /**
     * Customer type for company.
     *
     * @var string
     */
    public const CUSTOMER_TYPE_COMPANY = 'LEGAL';

    /**
     * Customer type for private citizens.
     *
     * @var string
     */
    public const CUSTOMER_TYPE_PRIVATE = 'NATURAL';

    /**
     * @var Credentials
     */
    public $credentials;

    /**
     * @var CoreApi
     */
    public $coreApi;

    /**
     * @inheritDoc
     */
    public function __construct(
        Context $context,
        Credentials $credentials,
        CoreApi $coreApi
    ) {
        $this->coreApi = $coreApi;
        $this->credentials = $credentials;

        parent::__construct($context);
    }

    /**
     * Fetches a customer address using a valid government ID from Resurs Bank's
     * API.
     *
     * @param string $governmentId
     * @param bool $isCompany
     * @return ApiAddress
     * @throws ValidatorException
     * @throws ApiDataException
     * @throws Exception
     */
    public function fetch(
        string $governmentId,
        bool $isCompany
    ): ApiAddress {
        $connection = $this->coreApi->getConnection(
            $this->credentials->resolveFromConfig()
        );

        $address = $connection->getAddress(
            $governmentId,
            $this->getCustomerType($isCompany)
        );

        if (!is_object($address)) {
            throw new ApiDataException(__('Failed to fetch address.'));
        }

        return $this->toAddress($address, $isCompany);
    }

    /**
     * @param ApiAddress $address
     * @return CheckoutAddress
     * @throws ApiDataException
     */
    public function toCheckoutAddress(
        ApiAddress $address
    ): CheckoutAddress {
        return new CheckoutAddress(
            $address->getFirstName(),
            $address->getLastName(),
            $address->getPostalArea(),
            $address->getPostalCode(),
            $address->getCountry(),
            $address->getAddressRow1(),
            $address->getAddressRow2(),
            $address->getIsCompany() ?
                $address->getFullName() :
                ''
        );
    }

    /**
     * Creates address model data from a generic object. Expects the generic
     * object to have the same properties as address data fetched from the API,
     * but it's not required to. Missing properties will be created using
     * default values.
     *
     * @param bool|null $isCompany
     * @param stdClass $address
     * @return ApiAddress
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function toAddress(
        stdClass $address,
        bool $isCompany = null
    ): ApiAddress {
        $hasFullName = property_exists($address, 'fullName');

        return new ApiAddress(
            (
                $isCompany === null &&
                $hasFullName &&
                (string) $address->fullName !== ''
            ) || $isCompany,
            $hasFullName ?
                (string) $address->fullName :
                '',
            property_exists($address, 'firstName') ?
                (string) $address->firstName :
                '',
            property_exists($address, 'lastName') ?
                (string) $address->lastName :
                '',
            property_exists($address, 'addressRow1') ?
                (string) $address->addressRow1 :
                '',
            property_exists($address, 'addressRow2') ?
                (string) $address->addressRow2 :
                '',
            property_exists($address, 'postalArea') ?
                (string) $address->postalArea :
                '',
            property_exists($address, 'postalCode') ?
                (string) $address->postalCode :
                '',
            property_exists($address, 'country') ?
                (string) $address->country :
                ''
        );
    }

    /**
     * Returns the customer type based on a boolean value which states what
     * type you're looking for.
     *
     * @param bool $isCompany
     * @return string
     */
    public function getCustomerType(bool $isCompany): string
    {
        return $isCompany ?
            self::CUSTOMER_TYPE_COMPANY :
            self::CUSTOMER_TYPE_PRIVATE;
    }
}
