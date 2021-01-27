<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Controller\Checkout;

use Exception;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\ValidatorException;
use Resursbank\Core\Exception\ApiDataException;
use Resursbank\Simplified\Exception\InvalidDataException;
use Resursbank\Simplified\Exception\MissingRequestParameterException;
use Resursbank\Simplified\Helper\Address as AddressHelper;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Request;

/**
 * Fetch customer address from API using supplied SSN and customer type.
 */
class FetchAddress implements HttpPostActionInterface
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * @var Request
     */
    private $requestHelper;

    /**
     * @param Log $log
     * @param AddressHelper $fetchAddressHelper
     * @param Request $request
     */
    public function __construct(
        Log $log,
        AddressHelper $fetchAddressHelper,
        Request $request
    ) {
        $this->log = $log;
        $this->addressHelper = $fetchAddressHelper;
        $this->requestHelper = $request;
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
     * @return array
     * @throws ApiDataException
     * @throws ValidatorException
     * @throws InvalidDataException
     * @throws MissingRequestParameterException
     */
    private function getAddress(): array
    {
        $isCompany = $this->requestHelper->isCompany();

        return $this->addressHelper
            ->toCheckoutAddress(
                $this->addressHelper->fetch(
                    $this->requestHelper->getGovId($isCompany),
                    $isCompany
                )
            )
            ->toArray();
    }
}
