<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Controller\Checkout;

use Exception;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Exception\MissingRequestParameterException;
use Resursbank\Simplified\Helper\Address as AddressHelper;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\ValidateGovernmentId;

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
     * @var ValidateGovernmentId
     */
    private $validateGovId;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param Log $log
     * @param AddressHelper $fetchAddressHelper
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param ValidateGovernmentId $validateGovId
     */
    public function __construct(
        Log $log,
        AddressHelper $fetchAddressHelper,
        ResultFactory $resultFactory,
        RequestInterface $request,
        ValidateGovernmentId $validateGovId
    ) {
        $this->log = $log;
        $this->addressHelper = $fetchAddressHelper;
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->validateGovId = $validateGovId;
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

        try {
            /** @var Json $response */
            $response = $this->resultFactory->create(
                ResultFactory::TYPE_JSON
            );
        } catch (Exception $e) {
            $this->log->exception($e);

            throw $e;
        }

        try {
            $idNum = $this->request->getParam('id_num');
            $isCompany = $this->getIsCompany();

            if (!is_bool($isCompany)) {
                throw new MissingRequestParameterException(
                    __('Parameter [is_company] was not set or isn\'t a bool.')
                );
            }

            if (!is_string($idNum)) {
                throw new MissingRequestParameterException(
                    __('Parameter [id_num] was not set or isn\'t a string.')
                );
            }

            if (!$this->validateGovId->sweden($idNum, $isCompany)) {
                throw new InvalidDataException(
                    __('Invalid swedish government ID was given.')
                );
            }

            $data['address'] = $this->addressHelper
                ->toCheckoutAddress(
                    $this->addressHelper->fetch($idNum, $isCompany)
                )
                ->toArray();
        } catch (Exception $e) {
            $this->log->exception($e);
            $data['error']['message'] = __(
                'Something went wrong when fetching the address. Please ' .
                'try again.'
            );
        }

        $response->setData($data);

        return $response;
    }

    /**
     * Converts and returns the is_company request parameter as a bool, if
     * possible.
     *
     * @return bool|null - A bool if the parameter can be converted, null if
     * the parameter wasn't set or isn't a bool.
     */
    private function getIsCompany(): ?bool
    {
        $isCompany = $this->request->getParam('is_company');
        $result = null;

        if ($isCompany === 'true') {
            $result = true;
        } elseif ($isCompany === 'false') {
            $result = false;
        }

        return $result;
    }
}
