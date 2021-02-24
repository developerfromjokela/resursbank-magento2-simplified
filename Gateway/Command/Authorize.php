<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Gateway\Command;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment;
use Resursbank\Core\Exception\PaymentDataException;
use Resursbank\Core\Helper\Api;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\RBEcomPHP\RESURS_FLOW_TYPES;
use Resursbank\RBEcomPHP\ResursBank;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Payment as PaymentHelper;
use Resursbank\Simplified\Helper\Session as CheckoutSession;

/**
 * Create payment session at Resurs Bank and prepare redirecting client to the
 * gateway for payment processing.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Authorize implements CommandInterface
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @var Api
     */
    private $api;

    /**
     * @var Credentials
     */
    private $credentials;

    /**
     * @var CheckoutSession
     */
    private $session;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @param Log $log
     * @param Api $api
     * @param Credentials $credentials
     * @param CheckoutSession $session
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        Log $log,
        Api $api,
        Credentials $credentials,
        CheckoutSession $session,
        PaymentHelper $paymentHelper
    ) {
        $this->api = $api;
        $this->log = $log;
        $this->credentials = $credentials;
        $this->session = $session;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * @param array<string, mixed> $subject
     * @return ResultInterface|null
     * @throws Exception
     */
    public function execute(
        array $subject
    ): ?ResultInterface {
        try {
            $payment = SubjectReader::readPayment($subject)->getPayment();

            if ($payment instanceof Payment) {
                $order = $payment->getOrder();

                // Establish API connection.
                $connection = $this->getConnection();

                // Apply payload data.
                $this->setPayloadData($order, $connection);

                // Create payment session at Resurs Bank and prepare signing.
                $this->createPayment($order, $connection);

                // Clear Resurs Bank related data from session.
                $this->session->unsetCustomerInfo();
            }
        } catch (Exception $e) {
            $this->log->exception($e);

            throw new PaymentException(__(
                'Something went wrong when trying to place the order. ' .
                'Please try again, or select another payment method. You ' .
                'could also try refreshing the page.'
            ));
        }

        return null;
    }

    /**
     * Resolve API connection.
     *
     * @return ResursBank
     * @throws ValidatorException
     * @throws Exception
     */
    private function getConnection(): ResursBank
    {
        try {
            $connection = $this->api->getConnection(
                $this->credentials->resolveFromConfig()
            );

            $connection->setPreferredPaymentFlowService(
                RESURS_FLOW_TYPES::SIMPLIFIED_FLOW
            );
        } catch (Exception $e) {
            // NOTE: Actual Exception is logged upstream.
            $this->log->error('Failed to establish a connection to the API');

            throw $e;
        }

        return $connection;
    }

    /**
     * Apply data to API payload in preparation of payment session creation.
     *
     * @param OrderInterface $order
     * @param ResursBank $connection
     * @throws PaymentDataException
     * @throws Exception
     */
    private function setPayloadData(
        OrderInterface $order,
        ResursBank $connection
    ): void {
        try {
            $this->paymentHelper
                ->setCustomer($order, $connection)
                ->setCardData($connection)
                ->setBillingAddress($order, $connection)
                ->setShippingAddress($order, $connection)
                ->addOrderLines($connection)
                ->setOrderId($order, $connection)
                ->setSigningUrls($connection, $this->session->getQuote())
                ->setPaymentData($connection);
        } catch (Exception $e) {
            // NOTE: Actual Exception is logged upstream.
            $this->log->error('Failed to apply API payload data.');

            throw $e;
        }
    }

    /**
     * Create payment session at Resurs Bank.
     *
     * NOTE: This basically creates a pending payment. The payment will be
     * registered (activated) when we reach the success page.
     *
     * @param OrderInterface $order
     * @param ResursBank $connection
     * @throws NoSuchEntityException
     * @throws PaymentDataException
     */
    private function createPayment(
        OrderInterface $order,
        ResursBank $connection
    ): void {
        try {
            // Create payment session at Resurs Bank.
            $payment = $this->paymentHelper->createPaymentSession(
                $order,
                $connection
            );

            // Reject denied payment.
            if ($payment->getBookPaymentStatus() === 'DENIED') {
                throw new PaymentDataException(__('Payment denied.'));
            }

            // Prepare redirecting client to gateway.
            $this->paymentHelper->prepareRedirect($payment);
        } catch (Exception $e) {
            $this->log->error('Failed to create payment');

            throw $e;
        }
    }
}
