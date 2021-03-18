<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Controller\Checkout;

use Exception;
use JsonException;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Model\PaymentMethodRepository;
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
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepo;

    /**
     * @param Log $log
     * @param CheckoutSession $session
     * @param Request $requestHelper
     * @param PaymentMethodRepository $paymentMethodRepo
     */
    public function __construct(
        Log $log,
        CheckoutSession $session,
        Request $requestHelper,
        PaymentMethodRepository $paymentMethodRepo
    ) {
        $this->log = $log;
        $this->session = $session;
        $this->requestHelper = $requestHelper;
        $this->paymentMethodRepo = $paymentMethodRepo;
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
            $methodCode = $this->requestHelper->getMethodCode();

            if (is_string($methodCode) &&
                !$this->isValidMethod($methodCode, $isCompany)
            ) {
                throw new InvalidDataException(__(
                    'The selected payment method is not available for the ' .
                    'selected customer type.'
                ));
            }

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

    /**
     * Validates that a payment method is usable by the customer by comparing
     * the customer type of the method to the type the customer has selected.
     *
     * @param string $methodCode
     * @param bool $isCompany
     * @return bool
     * @throws JsonException
     * @throws NoSuchEntityException
     */
    public function isValidMethod(
        string $methodCode,
        bool $isCompany
    ): bool {
        $result = false;
        $method = $this->paymentMethodRepo->getByCode($methodCode);

        if ($method->getActive(false) && is_string($method->getRaw())) {
            $raw = json_decode(
                $method->getRaw(),
                false,
                512,
                JSON_THROW_ON_ERROR
            );

            $customerType = $raw->customerType ?? '';
            $result = $isCompany ?
                $customerType === 'LEGAL' :
                $customerType === 'NATURAL';
        }

        return $result;
    }
}
