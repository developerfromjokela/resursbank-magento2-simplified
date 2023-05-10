<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Controller\Checkout;

use Exception;
use Resursbank\Core\Helper\Scope;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\ApiDataException;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Exception\MissingRequestParameterException;
use Resursbank\Core\Helper\Config;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Module\Customer\Repository;
use Resursbank\Simplified\Helper\Address as AddressHelper;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Request;

/**
 * Fetch customer address from API using supplied SSN and customer type.
 */
class FetchAddress implements HttpPostActionInterface
{
    /**
     * @param Log $log
     * @param AddressHelper $addressHelper
     * @param Request $request
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private Log $log,
        private AddressHelper $addressHelper,
        private Request $request,
        private Config $config,
        private StoreManagerInterface $storeManager,
        private Scope $scope,
        private Request $requestHelper,
    ) {
    }

    /**
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
        } catch (Exception $e) {
            $this->log->exception($e);

            // Display friendly (safe) error message to customer.
            $data['error']['message'] = __(
                'Something went wrong when fetching the address. Please ' .
                'try again.'
            );
        }

        return $this->requestHelper->getResponse($data);
    }

    /**
     * @return array<string, mixed>
     * @throws ApiDataException
     * @throws ValidatorException
     * @throws MissingRequestParameterException
     * @throws InvalidDataException
     * @throws NoSuchEntityException
     */
    private function getAddress(): array
    {
        $isCompany = $this->requestHelper->isCompany();

        if ($this->config->isMapiActive(
            scopeCode: $this->scope->getId(),
            scopeType: $this->scope->getType()
        )) {
            // Fetch address here
            $searchResult = Repository::getAddress(
                storeId: $this->config->getStore(
                    scopeCode: $this->scope->getId(),
                    scopeType: $this->scope->getType()
                ),
                customerType: ($isCompany ? CustomerType::LEGAL : CustomerType::NATURAL),
                governmentId: $this->requestHelper->getIdentifier($isCompany)
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
                    $this->addressHelper->fetch(
                        $this->requestHelper->getIdentifier($isCompany),
                        $isCompany
                    )
                )
                ->toArray();
        }

        return $response;
    }
}
