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
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\ApiDataException;
use Resursbank\Core\Helper\Api as CoreApi;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Model\Api\Address as ApiAddress;
use Resursbank\Simplified\Model\CheckoutAddress;
use function is_object;

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheritDoc
     */
    public function __construct(
        Context $context,
        Credentials $credentials,
        CoreApi $coreApi,
        StoreManagerInterface $storeManager
    ) {
        $this->coreApi = $coreApi;
        $this->credentials = $credentials;
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }

    /**
     * Fetches a customer address using a valid government ID from Resurs Banks
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
            $this->credentials->resolveFromConfig(
                $this->storeManager->getStore()->getCode(),
                ScopeInterface::SCOPE_STORES
            )
        );

        $address = $connection->getAddress(
            $governmentId,
            $this->getCustomerType($isCompany)
        );

        if (!is_object($address)) {
            throw new ApiDataException(__('Failed to fetch address.'));
        }

        return $this->coreApi->toAddress($address, $isCompany);
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
