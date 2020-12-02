<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Gateway\Command;

use Exception;
use Magento\Framework\Exception\PaymentException;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Resursbank\Core\Helper\Api;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\RBEcomPHP\RESURS_FLOW_TYPES;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Payment as PaymentHelper;
use Resursbank\Simplified\Helper\Session as CheckoutSession;

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
     *
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
     * @param array $commandSubject
     * @return ResultInterface|void|null
     * @throws Exception
     */
    public function execute(
        array $commandSubject
    ) {
        try {
            /** @var InfoInterface $payment */
            $payment = $commandSubject['payment']->getPayment();

            /** @var OrderInterface $order */
            $order = $payment->getOrder();
            $orderPayment = $order->getPayment();

            if ($orderPayment !== null) {
                $quote = $this->session->getQuote();
                $connection = $this->api->getConnection(
                    $this->credentials->resolveFromConfig()
                );

                $connection->setPreferredPaymentFlowService(
                    RESURS_FLOW_TYPES::SIMPLIFIED_FLOW
                );

                $this->paymentHelper
                    ->setCustomer($order, $connection)
                    ->setCardData($connection)
                    ->setBillingAddress($order, $connection)
                    ->setShippingAddress($order, $connection)
                    ->addOrderLines($connection)
                    ->setOrderId($order, $connection)
                    ->setSigningUrls($connection, $order, $quote)
                    ->setPaymentdata($connection);

                $payment = $this->paymentHelper->createPayment(
                    $order,
                    $connection
                );

                $this->paymentHelper->handlePaymentStatus($payment, $order);
                $this->paymentHelper->prepareSigning($payment);
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
}
