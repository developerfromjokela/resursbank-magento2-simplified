<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\ValidatorException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Resursbank\Core\Helper\Api as CoreApi;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Model\Api\Payment\Converter\QuoteConverter;
use Resursbank\Core\Model\PaymentMethodRepository;
use Resursbank\RBEcomPHP\ResursBank;
use Resursbank\Core\Exception\PaymentDataException;
use Resursbank\Simplified\Helper\Address as AddressHelper;
use Resursbank\Simplified\Model\Api\Customer;
use Resursbank\Simplified\Model\Api\Payment as PaymentModel;
use ResursException;
use stdClass;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Payment extends AbstractHelper
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * @var QuoteConverter
     */
    private $quoteConverter;

    /**
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepo;

    /**
     * @var CoreApi
     */
    public $coreApi;

    /**
     * @var Credentials
     */
    private $credentials;

    /**
     * @param Credentials $credentials
     * @param Context $context
     * @param Session $session
     * @param AddressHelper $addressHelper
     * @param QuoteConverter $quoteConverter
     * @param PaymentMethodRepository $paymentMethodRepo
     * @param CoreApi $coreApi
     */
    public function __construct(
        Credentials $credentials,
        Context $context,
        Session $session,
        AddressHelper $addressHelper,
        QuoteConverter $quoteConverter,
        PaymentMethodRepository $paymentMethodRepo,
        CoreApi $coreApi
    ) {
        $this->session = $session;
        $this->addressHelper = $addressHelper;
        $this->quoteConverter = $quoteConverter;
        $this->paymentMethodRepo = $paymentMethodRepo;
        $this->coreApi = $coreApi;
        $this->credentials = $credentials;

        parent::__construct($context);
    }

    /**
     * Set customer information with the order.
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
            (string) $this->session->getGovernmentId(),
            (string) $billingAddress->getTelephone(),
            (string) $billingAddress->getTelephone(),
            (string) $order->getCustomerEmail(),
            $this->session->getIsCompany() ? 'LEGAL' : 'NATURAL',
            (string) $this->session->getContactGovernmentId()
        );

        return $this;
    }

    /**
     * @param ResursBank $connection
     * @return self
     */
    public function setCardData(
        ResursBank $connection
    ): self {
        $connection->setCardData(
            (string) $this->session->getCardNumber(),
            (float) $this->session->getCardAmount()
        );

        return $this;
    }

    /**
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

        $connection->setBillingAddress(
            ($address->getFirstname() . ' ' . $address->getLastname()),
            $address->getFirstname(),
            $address->getLastname(),
            $address->getStreetLine(1),
            $address->getStreetLine(2),
            $address->getCity(),
            $address->getPostcode(),
            $address->getCountryId()
        );

        return $this;
    }

    /**
     * @param OrderInterface $order
     * @param ResursBank $connection
     * @return self
     * @throws PaymentDataException
     */
    public function setShippingAddress(
        OrderInterface $order,
        ResursBank $connection
    ): self {
        $address = $order->getShippingAddress() ?? $order->getBillingAddress();

        if (!($address instanceof OrderAddressInterface)) {
            throw new PaymentDataException(__(
                'The order does not have a billing address'
            ));
        }

        $connection->setDeliveryAddress(
            ($address->getFirstname() . ' ' . $address->getLastname()),
            $address->getFirstname(),
            $address->getLastname(),
            $address->getStreetLine(1),
            $address->getStreetLine(2),
            $address->getCity(),
            $address->getPostcode(),
            $address->getCountryId()
        );

        return $this;
    }

    /**
     * @param ResursBank $connection
     * @return self
     * @throws Exception
     */
    public function addOrderLines(
        ResursBank $connection
    ): self {
        $items = $this->quoteConverter->convert($this->session->getQuote());

        foreach ($items as $item) {
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
            $this->session->getSuccessCallbackUrl(
                (string) $quote->getId()
            ),
            $this->session->getFailureCallbackUrl(
                (string) $quote->getId()
            )
        );

        return $this;
    }

    /**
     * @param ResursBank $connection
     * @return self
     */
    public function setPaymentData(
        ResursBank $connection
    ): self {
        $connection->setWaitForFraudControl();
        $connection->setAnnulIfFrozen();
        $connection->setFinalizeIfBooked();

        return $this;
    }

    /**
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

        /** @var stdClass $payment */
        $payment = $connection->createPayment(
            $paymentMethod->getIdentifier()
        );

        return $this->toPayment($payment, $isCompany);
    }

    /**
     * @param string $paymentId
     * @return PaymentModel|null
     * @throws ResursException
     * @throws ValidatorException
     * @throws Exception
     */
    public function getPayment(
        string $paymentId
    ): ?PaymentModel {
        $connection = $this->coreApi->getConnection(
            $this->credentials->resolveFromConfig()
        );

        $payment = $connection->getPayment($paymentId);

        return $payment !== null ?
            $this->toPayment($payment) :
            null;
    }

    /**
     * @param PaymentModel $payment
     * @param array|string[] $reject
     * @return self
     * @throws PaymentDataException
     */
    public function handlePaymentStatus(
        PaymentModel $payment,
        array $reject = ['DENIED']
    ): self {
        // Handle reject statuses.
        if (in_array($payment->getBookPaymentStatus(), $reject, true)) {
            throw new PaymentDataException(__(
                'Your payment has been rejected, please select a ' .
                'different payment method and try again. If the problem ' .
                'persists please contact us for assistance.'
            ));
        }

        return $this;
    }

    /**
     * @param PaymentModel $payment
     * @return $this
     */
    public function prepareSigning(PaymentModel $payment): self
    {
        if ($payment->getSigningUrl() !== '') {
            // Store signing URL in session for later use to redirect client.
            // See Controller/Simplified/Redirect.php
            $this->session->setPaymentSigningUrl(
                $payment->getSigningUrl()
            );

            // Keep the resulting paymentId from creating the payment in mind.
            $this->session->setPaymentId(
                $payment->getPaymentId()
            );
        }

        return $this;
    }

    /**
     * Creates payment model data from a generic object. Expects the generic
     * object to have the same properties as payment data fetched from the API,
     * but it's not required to. Missing properties will be created using
     * default values.
     *
     * @param bool|null $isCompany
     * @param stdClass $payment
     * @return PaymentModel
     */
    public function toPayment(
        stdClass $payment,
        bool $isCompany = null
    ): PaymentModel {
        $paymentId = '';

        if (property_exists($payment, 'paymentId')) {
            $paymentId = (string) $payment->paymentId;
        } elseif (property_exists($payment, 'id')) {
            $paymentId = (string) $payment->id;
        }

        return new PaymentModel(
            $paymentId,
            property_exists(
                $payment,
                'bookPaymentStatus'
            ) ?
                (string) $payment->bookPaymentStatus :
                '',
            property_exists($payment, 'approvedAmount') ?
                (string) $payment->approvedAmount :
                '',
            property_exists($payment, 'signingUrl') ?
                (string) $payment->signingUrl :
                '',
            property_exists($payment, 'customer') ?
                $this->toCustomer(
                    $payment->customer,
                    $isCompany
                ) :
                new Customer(),
        );
    }

    /**
     * Creates customer model data from a generic object. Expects the generic
     * object to have the same properties as customer data fetched from the API,
     * but it's not required to. Missing properties will be created using
     * default values.
     *
     * @param bool|null $isCompany
     * @param stdClass $customer
     * @return Customer
     */
    public function toCustomer(
        stdClass $customer,
        bool $isCompany = null
    ): Customer {
        return new Customer(
            property_exists($customer, 'governmentId') ?
                (string) $customer->governmentId :
                '',
            property_exists($customer, 'phone') ?
                (string) $customer->phone :
                '',
            property_exists($customer, 'email') ?
                (string) $customer->email :
                '',
            property_exists($customer, 'type') ?
                (string) $customer->type :
                '',
            property_exists($customer, 'address') ?
                $this->addressHelper->toAddress(
                    $customer->address,
                    $isCompany
                ) :
                null
        );
    }

    /**
     * Book payment after it's been signed by the client.
     *
     * @param string $paymentId
     * @return PaymentModel
     * @throws Exception
     */
    public function bookPaymentSession(
        string $paymentId
    ): PaymentModel {
        // Fill data on payment object.
        $payment = $this->coreApi->getConnection(
            $this->credentials->resolveFromConfig()
        )->bookSignedPayment($paymentId);

        $result = $this->toPayment($payment);

        // Handle payment status (like "DENIED" etc.).
        $this->handlePaymentStatus(
            $result,
            ['DENIED', 'SIGNING']
        );

        return $result;
    }

    /**
     * @return ResursBank
     * @throws ValidatorException|Exception
     */
    public function getConnection(): ResursBank
    {
        return $this->coreApi->getConnection(
            $this->credentials->resolveFromConfig()
        );
    }
}
