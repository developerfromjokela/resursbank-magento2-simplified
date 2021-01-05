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
use Resursbank\Simplified\Exception\InvalidDataException;
use Resursbank\Simplified\Exception\MissingRequestParameterException;
use Resursbank\Simplified\Helper\FetchAddress as FetchAddressHelper;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\ValidateSsn;
use function is_bool;
use function is_string;

class FetchAddress implements HttpPostActionInterface
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @var FetchAddressHelper
     */
    private $fetchAddressHelper;

    /**
     * @var ValidateSsn
     */
    private $validateSsn;

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
     * @param FetchAddressHelper $fetchAddressHelper
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param ValidateSsn $validateSsn
     */
    public function __construct(
        Log $log,
        FetchAddressHelper $fetchAddressHelper,
        ResultFactory $resultFactory,
        RequestInterface $request,
        ValidateSsn $validateSsn
    ) {
        $this->log = $log;
        $this->fetchAddressHelper = $fetchAddressHelper;
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->validateSsn = $validateSsn;
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

        /** @noinspection BadExceptionsProcessingInspection */
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

            if (!$this->validateSsn->sweden($idNum, $isCompany)) {
                throw new InvalidDataException(
                    __('Invalid Swedish government ID was given.')
                );
            }

            $data['address'] = $this->fetchAddressHelper
                ->fetch($idNum, $isCompany)
                ->toArray();
        } catch (Exception $e) {
            // Log actual error.
            $this->log->exception($e);

            // Display harmless error to end client.
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
