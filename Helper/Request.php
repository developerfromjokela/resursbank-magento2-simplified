<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Exception;
use function is_bool;
use function is_string;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Exception\MissingRequestParameterException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Request extends AbstractHelper
{
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
     * @var ValidateCard
     */
    private $validateCard;

    /**
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param Log $log
     * @param RequestInterface $request
     * @param ValidateGovernmentId $validateGovernmentId
     * @param ValidateCard $validateCard
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        Log $log,
        RequestInterface $request,
        ValidateGovernmentId $validateGovernmentId,
        ValidateCard $validateCard
    ) {
        $this->resultFactory = $resultFactory;
        $this->log = $log;
        $this->request = $request;
        $this->validateGovernmentId = $validateGovernmentId;
        $this->validateCard = $validateCard;

        parent::__construct($context);
    }

    /**
     * Retrieve JSON response object.
     *
     * @param array<string, mixed> $data
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
     * @throws InvalidDataException
     * @throws MissingRequestParameterException
     */
    public function getContactGovId(): string
    {
        $result = $this->request->getParam('contact_gov_id');

        if (!is_string($result)) {
            throw new MissingRequestParameterException(__(
                'Parameter [contact_gov_id] was not set (or isn\'t a string) ' .
                'and is required when the customer is a company.'
            ));
        }

        if (!$this->validateGovernmentId->swedenSsn($result)) {
            throw new InvalidDataException(__(
                'Invalid Swedish government ID.'
            ));
        }

        return $result;
    }

    /**
     * Validates and returns "card_number" request parameter, if any.
     *
     * @return string|null - Null if the request parameter wasn't set.
     * @throws InvalidDataException
     */
    public function getCardNumber(): ?string
    {
        $result = $this->request->getParam('card_number');

        if (is_string($result) && !$this->validateCard->validate($result)) {
            throw new InvalidDataException(__('Invalid card number.'));
        }

        return is_string($result) ? $result : null;
    }

    /**
     * Converts and returns the "card_amount" request parameter as a float, if
     * possible.
     *
     * @return float|null - Null if the parameter can't be converted, or if it
     * wasn't set.
     * @throws InvalidDataException
     */
    public function getCardAmount(): ?float
    {
        $result = $this->request->getParam('card_amount');

        if ($result !== null && !is_numeric($result)) {
            throw new InvalidDataException(__('Invalid card amount.'));
        }

        return $result !== null ? (float) $result : null;
    }
}
