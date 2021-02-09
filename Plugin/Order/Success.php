<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Order;

use Exception;
use Magento\Checkout\Controller\Onepage\Success as OnepageSuccess;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Session\SuccessValidator;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\AddressRepository;
use Magento\Sales\Model\OrderRepository;
use Resursbank\Simplified\Exception\InvalidDataException;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Payment;
use Resursbank\Simplified\Helper\Session as SessionHelper;
use Resursbank\Simplified\Model\Api\Payment as PaymentModel;

/**
 * When utilising Simplified Flow the following actions will transpire at
 * order placement:
 *
 * 1. Order is created in Magento.
 * 2. Payment session is created (through bookPayment) at Resurs Bank.
 * 3. Client is redirected to Resurs Bank to sign their payment (normally
 * by using Bank ID).
 * 4. Client is redirected back to the success URL (we register the success
 * / failure URLs during step 2).
 * 5. API call bookSignedPayment is submitted to convert payment session to
 * an actual payment at Resurs Bank.
 * 6. The order is completed in Magento.
 *
 * Step 5 / 6 requires some data to be available in the client session, in
 * order to locate the quote and order corresponding to their purchase.
 *
 * This information will always be in the PHP session, unless they have
 * utilised a cell phone to perform their purchase. Consider a scenario
 * where the client opens their custom web browser (Chrome for example)
 * to assemble a shopping cart and place their order. Upon being asked to
 * sign for their purchase "Using Bank ID on this device" the client clicks
 * the popup to open their Bank ID application. After signing, the Bank ID
 * app will open a link to the success page. This link will be opened in the
 * default browser (for example Safari). Using this example, this means your
 * session data is not available since you performed your purchase using Chrome,
 * and thus step 5 and 6 fail.
 *
 * To circumvent this eventuality we include the clients quote_id in the
 * success / failure URLs registered in the payment session (see
 * Resursbank_Checkout_Model_Api :: getFailureCallbackUrl() /
 * getSuccessCallbackUrl()). We then add the information back to the
 * client session at pre-dispatch of the success / failure controller
 * actions.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Success
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var SessionHelper
     */
    private $sessionHelper;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var AddressRepository
     */
    private $addressRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @var SuccessValidator
     */
    private $successValidator;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param SearchCriteriaBuilder $searchBuilder
     * @param Log $log
     * @param RequestInterface $request
     * @param OrderRepository $orderRepository
     * @param Session $session
     * @param SessionHelper $sessionHelper
     * @param Payment $payment
     * @param AddressRepository $addressRepository
     * @param SuccessValidator $successValidator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        SearchCriteriaBuilder $searchBuilder,
        Log $log,
        RequestInterface $request,
        OrderRepository $orderRepository,
        Session $session,
        SessionHelper $sessionHelper,
        Payment $payment,
        AddressRepository $addressRepository,
        SuccessValidator $successValidator
    ) {
        $this->searchBuilder = $searchBuilder;
        $this->log = $log;
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->session = $session;
        $this->sessionHelper = $sessionHelper;
        $this->payment = $payment;
        $this->addressRepository = $addressRepository;
        $this->successValidator = $successValidator;
    }

    /**
     * @return void
     */
    public function beforeExecute(): void
    {
        try {
            $quoteId = $this->getQuoteIdFromRequest();

            if (!$this->successValidator->isValid()) {
                $this->restoreSession($quoteId);
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }
    }

    /**
     * @param OnepageSuccess $subject
     * @param ResultInterface $result
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterExecute(
        OnepageSuccess $subject,
        ResultInterface $result
    ): ResultInterface {
        /** @noinspection BadExceptionsProcessingInspection */
        try {
            $order = $this->getOrderByQuoteId($this->getQuoteIdFromRequest());

            $this->payment->bookPaymentSession($order->getIncrementId());
            $this->updateBillingAddress($order);
            $this->sessionHelper->unsetAll();
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * Try to update the billing address on the order entity to reflect the
     * address applied on the payment at Resurs Bank.
     *
     * NOTE: If there is an Exception during this process we will simply log the
     * error and leave the billing address applied on the order as-is. This is
     * by design since it's not vital for the address information to match
     * between the order and the payment, it's just more proper.
     *
     * @param OrderInterface $order
     * @return void
     */
    private function updateBillingAddress(
        OrderInterface $order
    ): void {
        try {
            $payment = $this->payment->getPayment($order->getIncrementId());

            if ($payment instanceof PaymentModel) {
                $this->overrideBillingAddress($payment, $order);
            }
        } catch (Exception $e) {
            $this->log->info(
                'Failed to update billing address on order ' .
                $order->getIncrementId() . '. The address on the payment at ' .
                'Resurs Bank may differ. The complete Exception will follow ' .
                'below.'
            );

            $this->log->exception($e);
        }
    }

    /**
     * Override billing address on order with information from Resurs Bank
     * payment. This is to ensure the customer's billing address in Magento
     * matches the address resolved by Resurs Bank when the payment is created.
     *
     * @param PaymentModel $payment
     * @param OrderInterface $order
     * @return void
     * @throws CouldNotSaveException
     */
    private function overrideBillingAddress(
        PaymentModel $payment,
        OrderInterface $order
    ): void {
        $billingAddress = $order->getBillingAddress();
        $paymentAddress = $payment->getCustomer()->getAddress();

        if ($billingAddress instanceof OrderAddressInterface) {
            if ($payment->getCustomer()->isCompany()) {
                $billingAddress->setCompany($paymentAddress->getFullName());
            } else {
                $billingAddress
                    ->setFirstname($paymentAddress->getFirstName())
                    ->setLastname($paymentAddress->getLastName());
            }

            $billingAddress
                ->setStreet([
                    $paymentAddress->getAddressRow1(),
                    $paymentAddress->getAddressRow2()
                ])
                ->setPostcode($paymentAddress->getPostalCode())
                ->setCity($paymentAddress->getPostalArea())
                ->setCountryId($paymentAddress->getCountry());

            $this->addressRepository->save($billingAddress);

            // Ensure the address is applied on the order entity (without
            // this "bill to name" in the order grid would for example give the
            // previous value).
            $order->setBillingAddress($billingAddress);
        }
    }

    /**
     * @param int $quoteId
     * @return OrderInterface
     * @throws InvalidDataException
     */
    private function getOrderByQuoteId(
        int $quoteId
    ): OrderInterface {
        $orderList = $this->orderRepository->getList(
            $this->searchBuilder
                ->addFilter('quote_id', $quoteId, 'eq')
                ->create()
        )->getItems();

        $order = end($orderList);

        if (!($order instanceof OrderInterface)) {
            throw new InvalidDataException(__(
                'Order with quote ID: ' .
                $quoteId .
                ' could not be found in the database.'
            ));
        }

        if ((int) $order->getEntityId() === 0) {
            throw new InvalidDataException(__(
                'The order does not have a valid entity ID.'
            ));
        }

        return $order;
    }

    /**
     * Restores the checkout session based on relevant values gathered from a
     * quote and its corresponding order.
     *
     * If the session has been lost during the signing process (likely due to
     * switching browsers), we need to restore specific session values to
     * ensure Magento handles the success / failure procedure correctly.
     *
     * @param int $quoteId
     * @return void
     * @throws InvalidDataException
     * @noinspection PhpUndefinedMethodInspection
     */
    private function restoreSession(
        int $quoteId
    ): void {
        $order = $this->getOrderByQuoteId($quoteId);

        $this->session->setLastQuoteId($quoteId);
        $this->session->setLastSuccessQuoteId($quoteId);
        $this->session->setLastOrderId($order->getEntityId());
        $this->session->setLastRealOrderId($order->getIncrementId());
    }

    /**
     * @return int
     * @throws InvalidDataException
     */
    private function getQuoteIdFromRequest(): int
    {
        $quoteId = (int) $this->request->getParam('quote_id');

        if ($quoteId === 0) {
            throw new InvalidDataException(__(
                'Quote ID 0 (zero) is an invalid ID.'
            ));
        }

        return $quoteId;
    }
}
