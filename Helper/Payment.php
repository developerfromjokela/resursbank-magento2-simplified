<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Exception;
use InvalidArgumentException;
use function is_string;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Exception\PaymentDataException;
use Resursbank\Core\Helper\Api as CoreApi;
use Resursbank\Core\Helper\Url;
use Resursbank\Core\Model\Api\Payment as PaymentModel;
use Resursbank\Core\Model\Api\Payment\Converter\QuoteConverter;
use Resursbank\Core\Model\PaymentMethodRepository;
use Resursbank\Core\Helper\Order as OrderHelper;
use Resursbank\RBEcomPHP\ResursBank;
use Resursbank\Simplified\Helper\Config as ConfigHelper;
use stdClass;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @noinspection EfferentObjectCouplingInspection
 */
class Payment extends AbstractHelper
{
    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var ConfigHelper
     */
    private ConfigHelper $configHelper;

    /**
     * @var QuoteConverter
     */
    private QuoteConverter $quoteConverter;

    /**
     * @var PaymentMethodRepository
     */
    private PaymentMethodRepository $paymentMethodRepo;

    /**
     * @var CoreApi
     */
    public CoreApi $coreApi;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Url
     */
    private Url $url;

    /**
     * @var OrderHelper
     */
    private OrderHelper $orderHelper;

    /**
     * @param Context $context
     * @param Session $session
     * @param QuoteConverter $quoteConverter
     * @param PaymentMethodRepository $paymentMethodRepo
     * @param CoreApi $coreApi
     * @param Config $configHelper
     * @param StoreManagerInterface $storeManager
     * @param Url $url
     * @param OrderHelper $orderHelper
     */
    public function __construct(
        Context $context,
        Session $session,
        QuoteConverter $quoteConverter,
        PaymentMethodRepository $paymentMethodRepo,
        CoreApi $coreApi,
        ConfigHelper $configHelper,
        StoreManagerInterface $storeManager,
        Url $url,
        OrderHelper $orderHelper
    ) {
        $this->session = $session;
        $this->quoteConverter = $quoteConverter;
        $this->paymentMethodRepo = $paymentMethodRepo;
        $this->coreApi = $coreApi;
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->orderHelper = $orderHelper;

        parent::__construct($context);
    }

    /**
     * Append customer information to API payload.
     *
     * @param OrderInterface $order
     * @param ResursBank $connection
     * @return self
     * @throws PaymentDataException
     * @throws Exception
     */
    public function setCustomer(
        OrderInterface $order,
        ResursBank $connection
    ): self {
        $billingAddress = $order->getBillingAddress();

        if (!($billingAddress instanceof OrderAddressInterface)) {
            throw new PaymentDataException(__(
                'The order did not have a billing address'
            ));
        }

        $connection->setCustomer(
            (string) $this->session->getGovId(),
            (string) $billingAddress->getTelephone(),
            (string) $billingAddress->getTelephone(),
            (string) $order->getCustomerEmail(),
            $this->session->getIsCompany() ? 'LEGAL' : 'NATURAL',
            (string) $this->session->getContactGovId()
        );

        return $this;
    }

    /**
     * Append billing address information to API payload.
     *
     * @param OrderInterface $order
     * @param ResursBank $connection
     * @return self
     * @throws PaymentDataException
     */
    public function setBillingAddress(
        OrderInterface $order,
        ResursBank $connection
    ): self {
        $address = $order->getBillingAddress();

        if (!($address instanceof OrderAddressInterface)) {
            throw new PaymentDataException(__(
                'The order did not have a billing address'
            ));
        }

        $street = $address->getStreet();

        $connection->setBillingAddress(
            ($address->getFirstname() . ' ' . $address->getLastname()),
            $address->getFirstname(),
            $address->getLastname(),
            $street[0] ?? '',
            $street[1] ?? '',
            $address->getCity(),
            $address->getPostcode(),
            $address->getCountryId()
        );

        return $this;
    }

    /**
     * Append delivery (shipping) address information to API payload.
     *
     * @param OrderInterface $order
     * @param ResursBank $connection
     * @return self
     * @throws PaymentDataException
     */
    public function setShippingAddress(
        OrderInterface $order,
        ResursBank $connection
    ): self {
        if (!($order instanceof Order)) {
            throw new InvalidArgumentException(
                'Sales/Model/Order instance required.'
            );
        }

        $address = $order->getShippingAddress() ?? $order->getBillingAddress();

        if (!($address instanceof OrderAddressInterface)) {
            throw new PaymentDataException(__(
                'The order does not have a shipping or billing address'
            ));
        }

        $street = $address->getStreet();

        $connection->setDeliveryAddress(
            ($address->getFirstname() . ' ' . $address->getLastname()),
            $address->getFirstname(),
            $address->getLastname(),
            $street[0] ?? '',
            $street[1] ?? '',
            $address->getCity(),
            $address->getPostcode(),
            $address->getCountryId()
        );

        return $this;
    }

    /**
     * Append items (cart/order) to API payload.
     *
     * @param ResursBank $connection
     * @return self
     * @throws Exception
     */
    public function addOrderLines(
        ResursBank $connection
    ): self {
        $items = $this->quoteConverter->convert($this->session->getQuote());

        foreach ($items as $item) {
            /** @phpstan-ignore-next-line */
            $connection->addOrderLine(
                $item->getArtNo(),
                $item->getDescription(),
                $item->getUnitAmountWithoutVat(),
                $item->getVatPct(),
                $item->getUnitMeasure(),
                $item->getType(),
                $item->getQuantity()
            );
        }

        return $this;
    }

    /**
     * Apply desired payment reference in API payload (ie. this is the reference
     * the payment will be created with at Resurs Bank, instead of a unique,
     * random, value which would otherwise be utilised).
     *
     * @param OrderInterface $order
     * @param ResursBank $connection
     * @return self
     */
    public function setOrderId(
        OrderInterface $order,
        ResursBank $connection
    ): self {
        $connection->setPreferredId($order->getIncrementId());

        return $this;
    }

    /**
     * Apply URL:s to be utilised when signing succeeds / fails (ie. these URL:s
     * will be triggered by the gateway after the client performs the payment).
     *
     * @param ResursBank $connection
     * @param Quote $quote
     * @return self
     * @throws Exception
     */
    public function setSigningUrls(
        ResursBank $connection,
        Quote $quote
    ): self {
        $connection->setSigning(
            $this->url->getSuccessUrl((int) $quote->getId()),
            $this->url->getFailureUrl((int) $quote->getId())
        );

        return $this;
    }

    /**
     * Apply payment handling flags.
     *
     * @param ResursBank $connection
     * @return self
     * @throws NoSuchEntityException
     */
    public function setPaymentData(
        ResursBank $connection
    ): self {
        $storeCode = $this->storeManager->getStore()->getCode();

        // Wait for fraud controls to be performed.
        $connection->setWaitForFraudControl(
            $this->configHelper->isWaitingForFraudControl($storeCode)
        );

        // Automatically annul payment if it becomes [FROZEN].
        $connection->setAnnulIfFrozen(
            $this->configHelper->isWaitingForFraudControl($storeCode) &&
            $this->configHelper->isAnnulIfFrozen($storeCode)
        );

        // Automatically finalize payment if it becomes [BOOKED].
        $connection->setFinalizeIfBooked(
            $this->configHelper->isFinalizeIfBooked($storeCode)
        );

        return $this;
    }

    /**
     * Create payment session at Resurs Bank.
     *
     * NOTE: This basically creates a pending payment. The payment will be
     * registered (activated) when we reach the success page (see
     * setSigningUrls method in this class).
     *
     * @param OrderInterface $order
     * @param ResursBank $connection
     * @return PaymentModel
     * @throws NoSuchEntityException
     * @throws PaymentDataException
     * @throws Exception
     */
    public function createPaymentSession(
        OrderInterface $order,
        ResursBank $connection
    ): PaymentModel {
        $isCompany = $this->session->getIsCompany();
        $orderPayment = $order->getPayment();

        if (!($orderPayment instanceof OrderPaymentInterface)) {
            throw new PaymentDataException(__(
                'The order does not have a payment.'
            ));
        }

        $paymentMethod = $this->paymentMethodRepo->getByCode(
            $orderPayment->getMethod()
        );

        $identifier = $paymentMethod->getIdentifier();

        if (!is_string($identifier)) {
            throw new InvalidDataException(__(
                'Payment method does not have an identifier.'
            ));
        }

        /** @var stdClass $payment */
        $payment = $connection->createPayment($identifier);

        return $this->coreApi->toPayment($payment, $isCompany);
    }

    /**
     * Prepare redirecting client to gateway to perform payment. When creating
     * a payment session at Resurs Bank we attain some values we will need to
     * store in our PHP session for later use (see
     * Controller/Simplified/Redirect.php) when redirecting the client.
     *
     * @param PaymentModel $payment
     * @return self
     */
    public function prepareRedirect(
        PaymentModel $payment
    ): self {
        if ($payment->getSigningUrl() !== '') {
            $this->session->setPaymentSigningUrl($payment->getSigningUrl());
            $this->session->setPaymentId($payment->getPaymentId());
        }

        return $this;
    }

    /**
     * Book payment after it's been signed by the client.
     *
     * @param OrderInterface $order
     * @return PaymentModel
     * @throws Exception
     */
    public function bookPaymentSession(
        OrderInterface $order
    ): PaymentModel {
        // Establish API connection.
        $connection = $this->coreApi->getConnection(
            $this->coreApi->getCredentialsFromOrder($order)
        );

        // Resolve order reference.
        $orderId = $order->getIncrementId();

        if ($orderId === null || $orderId === '') {
            throw new InvalidDataException(__('Missing order reference.'));
        }

        // Fill data on payment object.
        $payment = $this->coreApi->toPayment(
            $connection->bookSignedPayment($orderId)
        );

        // Reject denied / failed payment.
        switch ($payment->getBookPaymentStatus()) {
            case 'DENIED':
                $this->orderHelper->setCreditDeniedStatus($order);
                throw new PaymentDataException(__(
                    'Your credit application was denied, please select a ' .
                    'different payment method.'
                ));
            case 'SIGNING':
                throw new PaymentDataException(__('Payment failed.'));
        }

        return $payment;
    }
}
