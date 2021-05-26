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
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Helper\Order;
use Resursbank\Core\Helper\Request;
use Resursbank\Simplified\Helper\Config;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Payment;

/**
 * Book the payment at Resurs Bank after signing it (i.e. create payment).
 */
class BookSignedPayment
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Session
     */
    private $session;

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
        Session $session
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

            if ($this->config->isActive($storeCode)) {
                $this->payment->bookPaymentSession(
                    $this->order->getOrderByQuoteId(
                        $this->request->getQuoteId()
                    )
                );
            }
        } catch (Exception $e) {
            $this->log->exception($e);

            // Cancel to order (so it won't be left as pending / processing).
            $this->cancelOrder();

            // Because the message bag is not rendered on the failure page.
            /** @noinspection PhpUndefinedMethodInspection */
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
            $order = $this->order->getOrderByQuoteId(
                $this->request->getQuoteId()
            );

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
}
