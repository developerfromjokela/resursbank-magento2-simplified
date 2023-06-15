<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Controller\Checkout;

use Exception;
use JsonException;
use ReflectionException;
use Resursbank\Core\Helper\Scope;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Resursbank\Core\Exception\ApiDataException;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Exception\MissingRequestParameterException;
use Resursbank\Core\Helper\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\GetAddressException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Module\Customer\Repository;
use Resursbank\Simplified\Helper\Address as AddressHelper;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Request;
use Throwable;

/**
 * Fetch customer address from API using supplied SSN and customer type.
 */
class FetchAddress implements HttpPostActionInterface
{
    /**
     * @param Log $log
     * @param AddressHelper $addressHelper
     * @param Config $config
     * @param Scope $scope
     * @param Request $requestHelper
     */
    public function __construct(
        private readonly Log $log,
        private readonly AddressHelper $addressHelper,
        private readonly Config $config,
        private readonly Scope $scope,
        private readonly Request $requestHelper,
    ) {
    }

    /**
     * Execute.
     *
     * @throws Exception
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $data = [
            'address' => [],
            'error' => [
                'message' => ''
            ]
        ];

        // Resolve customer address.
        try {
            $data['address'] = $this->getAddress();
        } catch (Throwable $error) {
            $this->log->exception(error: $error);

            // Display friendly (safe) error message to customer.
            $data['error']['message'] = __(
                'Something went wrong when fetching the address. Please ' .
                'try again.'
            );
        }

        return $this->requestHelper->getResponse(data: $data);
    }

    /**
     * Get address data from API.
     *
     * @return array
     * @throws ApiDataException
     * @throws InvalidDataException
     * @throws MissingRequestParameterException
     * @throws NoSuchEntityException
     * @throws ValidatorException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws GetAddressException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     */
    private function getAddress(): array
    {
        $isCompany = $this->requestHelper->isCompany();

        if ($this->config->isMapiActive(
            scopeCode: $this->scope->getId(),
            scopeType: $this->scope->getType()
        )
        ) {
            $searchResult = Repository::getAddress(
                storeId: $this->config->getStore(
                    scopeCode: $this->scope->getId(),
                    scopeType: $this->scope->getType()
                ),
                customerType: ($isCompany ? CustomerType::LEGAL : CustomerType::NATURAL),
                governmentId: $this->requestHelper->getIdentifier(isCompany: $isCompany)
            );
            $response = [
                'firstname' => $searchResult->firstName,
                'lastname' => $searchResult->lastName,
                'city' => $searchResult->postalArea,
                'postcode' => $searchResult->postalCode,
                'country' => $searchResult->countryCode->value,
                'street0' => $searchResult->addressRow1,
                'street1' => $searchResult->addressRow2,
                'company' => '',
                'telephone' => ''
            ];
        } else {
            $response = $this->addressHelper
                ->toCheckoutAddress(
                    address: $this->addressHelper->fetch(
                        identifier: $this->requestHelper->getIdentifier(isCompany: $isCompany),
                        isCompany: $isCompany
                    )
                )
                ->toArray();
        }

        return $response;
    }
}
