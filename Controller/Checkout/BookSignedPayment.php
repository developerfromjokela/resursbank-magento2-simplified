<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Order;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\PaymentDataException;
use Resursbank\Core\Helper\Order;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Simplified\Helper\Config;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Payment;

/**
 * Book the payment at Resurs Bank after signing it (i.e. create payment).
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BookSignedPayment implements HttpPostActionInterface
{
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
     * @var Session
     */
    private Session $session;

    /**
     * @var PaymentMethods
     */
    private PaymentMethods $paymentMethods;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;

    /**
     * @param Log $log
     * @param Payment $payment
     * @param Order $order
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $url
     * @param Session $session
     * @param PaymentMethods $paymentMethods
     * @param RedirectFactory $redirectFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Log $log,
        Payment $payment,
        Order $order,
        Config $config,
        StoreManagerInterface $storeManager,
        UrlInterface $url,
        Session $session,
        PaymentMethods $paymentMethods,
        RedirectFactory $redirectFactory
    ) {
        $this->log = $log;
        $this->payment = $payment;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->session = $session;
        $this->paymentMethods = $paymentMethods;
        $this->order = $order;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * @return ResultInterface
     * @throws Exception
     */
    public function execute(): ResultInterface
    {
        $redirect = $this->redirectFactory->create();
        $status = '';

        try {
            $storeCode = $this->storeManager->getStore()->getCode();
            $order = $this->order->resolveOrderFromRequest();
            $payment = $order->getPayment();

            if ($payment === null ||
                !$this->config->isActive($storeCode) ||
                !$this->paymentMethods->isResursBankMethod($payment->getMethod())
            ) {
                throw new PaymentDataException(__(
                    'Payment was missing or was invalid when tried to book ' .
                    'signed payment.'
                ));
            }

            $bookedPayment = $this->payment->bookPaymentSession($order);
            $status = $bookedPayment->getBookPaymentStatus();

            // Reject denied / failed payment.
            switch ($status) {
                case 'DENIED':
                    $this->order->setCreditDeniedStatus($order);
                    throw new PaymentDataException(__(
                        'Your credit application was denied, please select a ' .
                        'different payment method.'
                    ));
                case 'SIGNING':
                    $redirect->setUrl(
                        $this->url->getUrl('checkout/checkout/redirect')
                    );
            }
        } catch (Exception $e) {
            $this->log->exception($e);
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

            // Redirect to failure page.
            $redirect->setUrl(
                $this->url->getUrl('checkout/onepage/failure')
            );
        }

        return $redirect;
    }

    /**
     * @return void
     */
    private function cancelOrder(): void
    {
        try {
            $this->order->cancelOrder(
                $this->order->resolveOrderFromRequest()
            );
        } catch (Exception $e) {
            $this->log->exception($e);
        }
    }
}
