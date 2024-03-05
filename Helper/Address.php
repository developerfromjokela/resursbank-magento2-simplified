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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\ApiDataException;
use Resursbank\Core\Helper\Api as CoreApi;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\Config as CoreConfig;
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
    public Credentials $credentials;

    /**
     * @var CoreApi
     */
    public CoreApi $coreApi;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var CoreConfig
     */
    private CoreConfig $config;

    /**
     * @param Context $context
     * @param Credentials $credentials
     * @param CoreApi $coreApi
     * @param StoreManagerInterface $storeManager
     * @param CoreConfig $config
     */
    public function __construct(
        Context $context,
        Credentials $credentials,
        CoreApi $coreApi,
        StoreManagerInterface $storeManager,
        CoreConfig $config
    ) {
        $this->coreApi = $coreApi;
        $this->credentials = $credentials;
        $this->storeManager = $storeManager;
        $this->config = $config;

        parent::__construct($context);
    }

    /**
     * Fetch address.
     *
     * Fetch address from the API using either government id (Sweden) or a phone
     * number (Norway).
     *
     * @param string $identifier
     * @param bool $isCompany
     * @return ApiAddress
     * @throws ApiDataException
     * @throws ValidatorException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function fetch(
        string $identifier,
        bool $isCompany
    ): ApiAddress {
        // What country the store is associated with.
        $country = $this->config->getDefaultCountry(
            $this->storeManager->getStore()->getCode()
        );

        // Establish API connection.
        $connection = $this->coreApi->getConnection(
            $this->credentials->resolveFromConfig(
                $this->storeManager->getStore()->getCode(),
                ScopeInterface::SCOPE_STORES
            )
        );

        // Customer type (NATURAL|LEGAL).
        $type = $this->getCustomerType($isCompany);

        // Raw address data resolved from the API.
        $address = null;

        // Fetch address data from the API.
        switch ($country) {
            case 'SE':
                $address = $connection->getAddress($identifier, $type);
                break;
            case 'NO':
                $address = $connection->getAddressByPhone($identifier, $type);
                break;
        }

        // Validate return value.
        if (!is_object($address)) {
            throw new ApiDataException(__('Failed to fetch address.'));
        }

        // Convert and return address data.
        return $this->coreApi->toAddress(
            $address,
            $isCompany,
            ($country === 'NO' ? $identifier : '')
        );
    }

    /**
     * Convert address to CheckoutAddress type.
     *
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
                '',
            $address->getTelephone()
        );
    }

    /**
     * Get customer type.
     *
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
