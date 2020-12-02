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
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Resursbank\Core\Model\Api\Payment\Converter\QuoteConverter;
use Resursbank\Core\Model\PaymentMethodRepository;
use Resursbank\RBEcomPHP\ResursBank;
use Resursbank\Simplified\Exception\PaymentDataException;
use Resursbank\Simplified\Helper\Session;
use Resursbank\Simplified\Helper\Address as AddressHelper;
use Resursbank\Simplified\Model\Api\Address;
use Resursbank\Simplified\Model\Api\Customer;
use Resursbank\Simplified\Model\Api\Payment as PaymentModel;
use stdClass;

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
    private $paymentMethodRepository;

    /**
     * @param Context $context
     * @param Session $session
     * @param AddressHelper $addressHelper
     * @param QuoteConverter $quoteConverter
     * @param PaymentMethodRepository $paymentMethodRepository
     */
    public function __construct(
        Context $context,
        Session $session,
        AddressHelper $addressHelper,
        QuoteConverter $quoteConverter,
        PaymentMethodRepository $paymentMethodRepository
    ) {
        $this->session = $session;
        $this->addressHelper = $addressHelper;
        $this->quoteConverter = $quoteConverter;
        $this->paymentMethodRepository = $paymentMethodRepository;

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
     * @param OrderInterface $order
     * @param Quote $quote
     * @return self
     * @throws Exception
     */
    public function setSigningUrls(
        ResursBank $connection,
        OrderInterface $order,
        Quote $quote
    ): self {
        $connection->setSigning(
            $this->session->getSuccessCallbackUrl(
                $order->getIncrementId(),
                (string) $quote->getId()
            ),
            $this->session->getFailureCallbackUrl(
                $order->getIncrementId(),
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
    public function createPayment(
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

        $paymentMethod = $this->paymentMethodRepository->getByCode(
            $orderPayment->getMethod()
        );

        /** @var stdClass $payment */
        $payment = $connection->createPayment(
            $paymentMethod->getIdentifier()
        );

        return $this->toPayment(
            $isCompany,
            $payment
        );
    }

    /**
     * @param PaymentModel $payment
     * @param OrderInterface $order
     * @param array|string[] $reject
     * @return self
     * @throws PaymentDataException
     */
    public function handlePaymentStatus(
        PaymentModel $payment,
        OrderInterface $order,
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
     * @param bool $isCompany
     * @param stdClass $payment
     * @return PaymentModel
     */
    public function toPayment(
        bool $isCompany,
        stdClass $payment
    ): PaymentModel {
        return new PaymentModel(
            property_exists($payment, 'paymentId') ?
                (string) $payment->paymentId :
                '',
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
                    $isCompany,
                    $payment->customer
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
     * @param bool $isCompany
     * @param stdClass $customer
     * @return Customer
     */
    public function toCustomer(
        bool $isCompany,
        stdClass $customer
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
                    $isCompany,
                    $customer->address
                ) :
                null
        );
    }
}
