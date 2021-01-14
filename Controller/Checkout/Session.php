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
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\ValidateGovernmentId;
use Resursbank\Simplified\Helper\ValidateCard;
use Resursbank\Simplified\Helper\Session as CheckoutSession;

class Session implements HttpPostActionInterface
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @var ValidateGovernmentId
     */
    private $validateGovId;

    /**
     * @var ValidateCard
     */
    private $validateCard;

    /**
     * @var CheckoutSession
     */
    private $session;

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
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param ValidateGovernmentId $validateGovId
     * @param ValidateCard $validateCard
     * @param CheckoutSession $session
     */
    public function __construct(
        Log $log,
        ResultFactory $resultFactory,
        RequestInterface $request,
        ValidateGovernmentId $validateGovId,
        ValidateCard $validateCard,
        CheckoutSession $session
    ) {
        $this->log = $log;
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->validateGovId = $validateGovId;
        $this->validateCard = $validateCard;
        $this->session = $session;
    }

    /**
     * @throws Exception
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $data = [
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
            $govId = $this->getGovernmentId();
            $contactId = $this->getContactGovernmentId();
            $cardAmount = $this->getCardAmount();
            $cardNumber = $this->getCardNumber();
            $isCompany = $this->getIsCompany();

            if (!is_bool($isCompany)) {
                throw new MissingRequestParameterException(__(
                    'Parameter [is_company] was not set or isn\'t a bool.'
                ));
            }

            if (!is_string($govId)) {
                throw new MissingRequestParameterException(__(
                    'Parameter [gov_id] was not set or isn\'t a string.'
                ));
            }

            if ($isCompany && !is_string($contactId)) {
                throw new MissingRequestParameterException(__(
                    'Parameter [contact_gov_id] was not set (or isn\'t a string) ' .
                    'and is required when the customer is a company.'
                ));
            }

            if (!$this->validateGovId->sweden($govId, $isCompany)) {
                throw new InvalidDataException(__(
                    'Invalid swedish government ID was given.'
                ));
            }

            if ($isCompany &&
                !$this->validateGovId->swedenSsn($contactId)
            ) {
                throw new InvalidDataException(__(
                    'Invalid swedish government ID was given.'
                ));
            }

            if ($cardNumber !== null &&
                !$this->validateCard->validate($cardNumber)
            ) {
                throw new InvalidDataException(__(
                    'Invalid card number was given.'
                ));
            }

            $this->session
                ->setGovernmentId($govId, $isCompany)
                ->setIsCompany($isCompany);

            if ($contactId !== null) {
                $this->session->setContactGovernmentId($contactId);
            }

            if ($cardNumber !== null) {
                $this->session->setCardNumber($cardNumber);
            }

            if ($cardAmount !== null) {
                $this->session->setCardAmount($cardAmount);
            }
        } catch (Exception $e) {
            $this->log->exception($e);
            $data['error']['message'] = __(
                'Something went wrong when trying to place the order. ' .
                'Please try again, or select another payment method. You ' .
                'could also try refreshing the page.'
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

    /**
     * Returns the "gov_id" request parameter as a string, if possible.
     *
     * @return string|null - Null if the parameter wasn't set.
     */
    private function getGovernmentId(): ?string
    {
        $value = $this->request->getParam('gov_id');

        return is_string($value) ? $value : null;
    }

    /**
     * Returns the "contact_gov_id" request parameter as a string, if possible.
     *
     * @return string|null - Null if the parameter wasn't set.
     */
    private function getContactGovernmentId(): ?string
    {
        $value = $this->request->getParam('contact_gov_id');

        return is_string($value) ? $value : null;
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
