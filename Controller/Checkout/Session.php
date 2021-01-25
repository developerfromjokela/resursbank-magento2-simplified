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
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Request;
use Resursbank\Simplified\Helper\Session as CheckoutSession;

/**
 * Store data supplied through inputs in the checkout process in the PHP session
 * for later usage.
 */
class Session implements HttpPostActionInterface
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @var CheckoutSession
     */
    private $session;

    /**
     * @var Request
     */
    private $requestHelper;

    /**
     * @param Log $log
     * @param CheckoutSession $session
     * @param Request $requestHelper
     */
    public function __construct(
        Log $log,
        CheckoutSession $session,
        Request $requestHelper
    ) {
        $this->log = $log;
        $this->session = $session;
        $this->requestHelper = $requestHelper;
    }

    /**
     * @throws Exception
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(): ResultInterface
    {
        $data = [
            'error' => [
                'message' => ''
            ]
        ];

        /** @noinspection BadExceptionsProcessingInspection */
        try {
            $isCompany = $this->requestHelper->isCompany();
            $cardAmount = $this->requestHelper->getCardAmount();
            $cardNumber = $this->requestHelper->getCardNumber();

            // Store government id and whether client is a company in session.
            $this->session
                ->setGovernmentId(
                    $this->requestHelper->getGovId($isCompany),
                    $isCompany
                )
                ->setIsCompany($isCompany);

            // If client is a company, store private reference SSN in session.
            if ($isCompany) {
                $this->session->setContactGovernmentId(
                    $this->requestHelper->getContactGovId()
                );
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

        return $this->requestHelper->getResponse($data);
    }
}
