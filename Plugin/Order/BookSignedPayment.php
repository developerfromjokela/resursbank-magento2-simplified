<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Order;

use Exception;
use Magento\Checkout\Controller\Onepage\Success;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Helper\Order;
use Resursbank\Core\Helper\Request;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Simplified\Helper\Config;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Payment;

/**
 * Book the payment at Resurs Bank after signing it (i.e. create payment).
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BookSignedPayment
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var Log
     */
    private Log $log;

    /**
     * @var Payment
     */
    private Payment $payment;

    /**
     * @var Order
     */
    private Order $order;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var PaymentMethods
     */
    private PaymentMethods $paymentMethods;

    /**
     * @param Log $log
     * @param Payment $payment
     * @param Request $request
     * @param Order $order
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $url
     * @param OrderRepositoryInterface $orderRepository
     * @param Session $session
     * @param PaymentMethods $paymentMethods
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Log $log,
        Payment $payment,
        Request $request,
        Order $order,
        Config $config,
        StoreManagerInterface $storeManager,
        UrlInterface $url,
        OrderRepositoryInterface $orderRepository,
        Session $session,
        PaymentMethods $paymentMethods
    ) {
        $this->log = $log;
        $this->payment = $payment;
        $this->request = $request;
        $this->order = $order;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->orderRepository = $orderRepository;
        $this->session = $session;
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @param Success $subject
     * @param ResultInterface $result
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterExecute(
        Success $subject,
        ResultInterface $result
    ): ResultInterface {
        try {
            $storeCode = $this->storeManager->getStore()->getCode();
            $order = $this->getOrder();
            $payment = $order->getPayment();

            if ($payment !== null &&
                $this->config->isActive($storeCode) &&
                $this->paymentMethods->isResursBankMethod($payment->getMethod())
            ) {
                $this->payment->bookPaymentSession($order);
            }
        } catch (Exception $e) {
            $this->log->exception($e);

            // Cancel to order (so it won't be left as pending / processing).
            $this->cancelOrder();

            // Because the message bag is not rendered on the failure page.
            /**
             * @noinspection PhpUndefinedMethodInspection
             * @phpstan-ignore-next-line
             */
            $this->session->setErrorMessage(__(
                'Something went wrong when completing your payment. Your ' .
                'order has been canceled. We apologize for this ' .
                'inconvenience, please try again.'
            ));

            // Redirect to failure page (without rebuilding the cart).
            $result->setHttpResponseCode(302)->setHeader(
                'Location',
                $this->url->getUrl(
                    'checkout/onepage/failure',
                    [
                        'disable_rebuild_cart' => 1
                    ]
                )
            );
        }

        return $result;
    }

    /**
     * @return void
     */
    private function cancelOrder(): void
    {
        try {
            $order = $this->getOrder();

            if (!($order instanceof OrderModel)) {
                throw new InvalidDataException(
                    __('Unexpected Order instance.')
                );
            }

            $this->orderRepository->save($order->cancel());
        } catch (Exception $e) {
            $this->log->exception($e);
        }
    }

    /**
     * @return OrderInterface
     * @throws InvalidDataException
     */
    private function getOrder(): OrderInterface
    {
        return $this->hasQuoteId() ?
            $this->order->getOrderByQuoteId(
                $this->request->getQuoteId()
            ) :
            $this->session->getLastRealOrder();
    }

    /**
     * @return bool
     */
    private function hasQuoteId(): bool
    {
        $result = true;

        try {
            $this->request->getQuoteId();
        } catch (Exception $e) {
            $result = false;
        }

        return $result;
    }
}
