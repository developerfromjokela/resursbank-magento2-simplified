<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Gateway\Command;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\PaymentDataException;
use Resursbank\Core\Gateway\Command\Authorize as Subject;
use Resursbank\Core\Helper\Api;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\RBEcomPHP\RESURS_FLOW_TYPES;
use Resursbank\RBEcomPHP\ResursBank;
use Resursbank\Simplified\Helper\Config;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Payment as PaymentHelper;
use Resursbank\Simplified\Helper\Session as CheckoutSession;

/**
 * Create payment session at Resurs Bank and prepare redirecting client to the
 * gateway for payment processing.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Authorize
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
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Log $log
     * @param Api $api
     * @param Credentials $credentials
     * @param CheckoutSession $session
     * @param PaymentHelper $paymentHelper
     * @param Config $config
     */
    public function __construct(
        Log $log,
        Api $api,
        Credentials $credentials,
        CheckoutSession $session,
        PaymentHelper $paymentHelper,
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->api = $api;
        $this->log = $log;
        $this->credentials = $credentials;
        $this->session = $session;
        $this->paymentHelper = $paymentHelper;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Subject $subject
     * @param array<mixed> $data
     * @return void
     * @throws PaymentException
     * @noinspection PhpUnusedParameterInspection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        Subject $subject,
        array $data
    ): void {
        try {
            $storeCode = $this->storeManager->getStore()->getCode();

            if ($this->config->isActive($storeCode)) {
                $payment = SubjectReader::readPayment($data)->getPayment();

                if ($payment instanceof Payment) {
                    $order = $payment->getOrder();

                    // Establish API connection.
                    $connection = $this->getConnection($order);

                    // Apply payload data.
                    $this->setPayloadData($order, $connection);

                    // Create payment session at Resurs Bank and prepare signing.
                    $this->createPayment($order, $connection);

                    // Clear Resurs Bank related data from session.
                    $this->session->unsetCustomerInfo();
                }
            }
        } catch (Exception $e) {
            $this->log->exception($e);

            throw new PaymentException(__(
                'Something went wrong when trying to place the order. ' .
                'Please try again, or select another payment method. You ' .
                'could also try refreshing the page.'
            ));
        }
    }

    /**
     * Resolve API connection.
     *
     * @param OrderInterface $order
     * @return ResursBank
     * @throws ValidatorException
     */
    private function getConnection(
        OrderInterface $order
    ): ResursBank {
        try {
            $connection = $this->api->getConnection(
                $this->credentials->resolveFromConfig(
                    (string) $order->getStoreId(),
                    ScopeInterface::SCOPE_STORES
                )
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