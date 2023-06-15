<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Controller\Checkout;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\PaymentDataException;
use Resursbank\Core\Helper\Order;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Simplified\Helper\Config;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Core\Helper\Url;
use Resursbank\Simplified\Helper\Payment;

/**
 * Book the payment at Resurs Bank after signing it (i.e. create payment).
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BookSignedPayment implements HttpGetActionInterface
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
     * @var PaymentMethods
     */
    private PaymentMethods $paymentMethods;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;

    /**
     * @var Url
     */
    private Url $urlHelper;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $eventManager;

    /**
     * @param Log $log
     * @param Payment $payment
     * @param Order $order
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $url
     * @param PaymentMethods $paymentMethods
     * @param RedirectFactory $redirectFactory
     * @param Url $urlHelper
     * @param ManagerInterface $eventManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Log $log,
        Payment $payment,
        Order $order,
        Config $config,
        StoreManagerInterface $storeManager,
        UrlInterface $url,
        PaymentMethods $paymentMethods,
        RedirectFactory $redirectFactory,
        Url $urlHelper,
        ManagerInterface $eventManager
    ) {
        $this->log = $log;
        $this->payment = $payment;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->paymentMethods = $paymentMethods;
        $this->order = $order;
        $this->redirectFactory = $redirectFactory;
        $this->urlHelper = $urlHelper;
        $this->eventManager = $eventManager;
    }

    /**
     * Execute.
     *
     * @return ResultInterface
     * @throws Exception
     */
    public function execute(): ResultInterface
    {
        $redirect = $this->redirectFactory->create();
        $quoteId = $this->order->getQuoteId();

        /** @noinspection BadExceptionsProcessingInspection */
        try {
            $order = $this->order->resolveOrderFromRequest();

            if (!$this->validate($order)) {
                throw new PaymentDataException(__('Invalid payment.'));
            }

            $this->eventManager->dispatch(
                'resursbank_book_signed_payment_before',
                ['order' => $order]
            );

            $bookedPayment = $this->payment->bookPaymentSession($order);

            $this->eventManager->dispatch(
                'resursbank_book_signed_payment_after',
                [
                    'order' => $order,
                    'paymentSession' => $bookedPayment,
                ]
            );

            switch ($bookedPayment->getBookPaymentStatus()) {
                case 'DENIED':
                    // Cancel order, mark it as denied.
                    $this->order->setCreditDeniedStatus($order);
                    throw new PaymentDataException(__(
                        'Your credit application was denied, please select a ' .
                        'different payment method.'
                    ));
                case 'SIGNING':
                    // Redirect client back to signing page.
                    $redirect->setUrl(
                        $this->url->getUrl(
                            'resursbank_simplified/checkout/redirect'
                        )
                    );
            }

            // Redirect to success page if status from bookPaymentResponse is
            // 'FROZEN', 'BOOKED' or 'FINALIZED'.
            $redirect->setUrl($this->urlHelper->getSuccessUrl($quoteId));
        } catch (Exception $e) {
            $this->log->exception($e);

            /* Make sure the order is cancelled, in case of an Exception
            occurring before or during the API call. */
            $this->cancelOrder();

            /* Redirect us to the failure page, which in turn will rebuild our
            shopping cart and redirect us to the checkout again. */
            $redirect->setUrl($this->urlHelper->getFailureUrl($quoteId));
        }

        return $redirect;
    }

    /**
     * Cancel an order.
     *
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

    /**
     * Make sure the payment associated with the supplied order utilises a
     * payment method from Resurs Bank, and that Simplified Flow is the
     * configured API.
     *
     * @param OrderInterface $order
     * @return bool
     * @throws NoSuchEntityException
     */
    private function validate(
        OrderInterface $order
    ): bool {
        $payment = $order->getPayment();

        return ($payment !== null &&
            $this->paymentMethods->isResursBankMethod($payment->getMethod()) &&
            $this->config->isActive($this->storeManager->getStore()->getCode())
        );
    }
}
