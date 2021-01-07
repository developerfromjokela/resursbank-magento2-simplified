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
use Resursbank\Simplified\Model\Api\Address;
use stdClass;

class FetchAddress extends AbstractHelper
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
     * @return Address
     * @throws ValidatorException
     * @throws ApiDataException
     * @throws Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function fetch(
        string $governmentId,
        bool $isCompany
    ): Address {
        // Setup API connection.
        $connection = $this->coreApi->getConnection(
            $this->credentials->resolveFromConfig()
        );

        // Fetch address data through API.
        $address = $connection->getAddress(
            $governmentId,
            $this->getCustomerType($isCompany)
        );

        // API lib should return an anonymous object instance containing address
        // data as anonymous properties.
        if (!$address instanceof stdClass) {
            throw new ApiDataException(__('Failed to fetch address from API.'));
        }

        return new Address(
            (isset($address->firstName) ? (string) $address->firstName : ''),
            (isset($address->lastName) ? (string) $address->lastName : ''),
            (isset($address->postalArea) ? (string) $address->postalArea : ''),
            (isset($address->postalCode) ? (string) $address->postalCode : ''),
            (isset($address->country) ? (string) $address->country : ''),
            (
                isset($address->addressRow1) ?
                    (string) $address->addressRow1 :
                    ''
            ),
            (
                isset($address->addressRow2) ?
                    (string) $address->addressRow2 :
                    ''
            ),
            (
                ($isCompany && isset($address->fullName)) ?
                    (string) $address->fullName :
                    ''
            )
        );
    }

    /**
     * Returns the customer type based on a boolean value which states what
     * type you're looking for.
     *
     * @param bool $isCompany
     * @return string
     */
    public function getCustomerType(
        bool $isCompany
    ): string {
        return $isCompany ?
            self::CUSTOMER_TYPE_COMPANY :
            self::CUSTOMER_TYPE_PRIVATE;
    }
}
