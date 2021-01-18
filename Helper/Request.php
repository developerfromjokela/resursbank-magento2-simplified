<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Resursbank\Simplified\Exception\InvalidDataException;
use Resursbank\Simplified\Exception\MissingRequestParameterException;
use Magento\Framework\App\RequestInterface;
use function is_bool;
use function is_string;

class Request extends AbstractHelper
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var ValidateGovernmentId
     */
    private $validateGovernmentId;

    /**
     * @param Context $context
     * @param CheckoutSession $sessionManager
     * @param ResultFactory $resultFactory
     * @param Log $log
     * @param RequestInterface $request
     * @param ValidateGovernmentId $validateGovernmentId
     */
    public function __construct(
        Context $context,
        CheckoutSession $sessionManager,
        ResultFactory $resultFactory,
        Log $log,
        RequestInterface $request,
        ValidateGovernmentId $validateGovernmentId
    ) {
        $this->checkoutSession = $sessionManager;
        $this->resultFactory = $resultFactory;
        $this->log = $log;
        $this->request = $request;
        $this->validateGovernmentId = $validateGovernmentId;

        parent::__construct($context);
    }

    /**
     * Retrieve JSON response object.
     *
     * @param array $data
     * @return Json
     * @throws Exception
     */
    public function getResponse(
        array $data
    ): Json {
        try {
            /** @var Json $result */
            $result = $this->resultFactory->create(
                ResultFactory::TYPE_JSON
            );

            $result->setData($data);
        } catch (Exception $e) {
            $this->log->exception($e);

            // Kill process.
            throw $e;
        }

        return $result;
    }

    /**
     * Retrieve the is_company request parameter as a bool.
     *
     * @return bool
     * @throws MissingRequestParameterException
     */
    public function isCompany(): bool
    {
        $result = $this->request->getParam('is_company');

        if ($result === 'true') {
            $result = true;
        } elseif ($result === 'false') {
            $result = false;
        }

        if (!is_bool($result)) {
            throw new MissingRequestParameterException(
                __('Parameter [is_company] was not set or isn\'t a bool.')
            );
        }

        return $result;
    }

    /**
     * Validates and returns the gov_id request parameter.
     *
     * @param bool $isCompany
     * @return string
     * @throws InvalidDataException
     * @throws MissingRequestParameterException
     */
    public function getGovId(
        bool $isCompany
    ): string {
        $result = $this->request->getParam('gov_id');

        if (!is_string($result)) {
            throw new MissingRequestParameterException(
                __('Parameter [gov_id] was not set or isn\'t a string.')
            );
        }

        if (!$this->validateGovernmentId->sweden($result, $isCompany)) {
            throw new InvalidDataException(
                __('Invalid Swedish government ID.')
            );
        }

        return $result;
    }

    /**
     * Returns the "contact_gov_id" request parameter as a string.
     *
     * NOTE: this value will only be present if the client is a company.
     *
     * @return string
     */
    private function getContactGovernmentId(): string
    {
        $value = $this->request->getParam('contact_gov_id');

        if (!is_string($result)) {
            throw new MissingRequestParameterException(
                __('Parameter [gov_id] was not set or isn\'t a string.')
            );
        }

        if (!$this->validateGovernmentId->sweden($result, $isCompany)) {
            throw new InvalidDataException(
                __('Invalid Swedish government ID.')
            );
        }
        
        return $value;
    }

    /**
     * Converts and returns the "card_amount" request parameter as a float, if
     * possible.
     *
     * @return float|null - Null if the parameter can't be converted, or if it
     * wasn't set.
     */
    private function getCardAmount(): ?float
    {
        $result = null;

        try {
            $value = $this->request->getParam('card_amount');
            $result = $value === null ? null : (float) $value;
        } catch (Exception $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * Converts and returns the "card_number" request parameter as a string, if
     * possible.
     *
     * @return string|null - Null if the parameter wasn't set.
     */
    private function getCardNumber(): ?string
    {
        $value = $this->request->getParam('card_number');

        return is_string($value) ? $value : null;
    }
}
