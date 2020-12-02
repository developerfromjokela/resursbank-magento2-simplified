<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Model\Api;

use Resursbank\Simplified\Model\Api\Customer;

class Payment
{
    /**
     * ID of the payment. Every created payment should also have an ID, without
     * it the checkout process cannot be completed.
     *
     * @var string
     */
    private $paymentId;

    /**
     * @var string
     */
    private $bookPaymentStatus;

    /**
     * @var string
     */
    private $approvedAmount;

    /**
     * The URL the customer will be redirected to to authorize the payment, the
     * payment gateway, in other words.
     *
     * @var string
     */
    private $signingUrl;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @param string $paymentId
     * @param string $bookPaymentStatus
     * @param string $approvedAmount
     * @param string $signingUrl
     * @param Customer|null $customer
     */
    public function __construct(
        string $paymentId = '',
        string $bookPaymentStatus = '',
        string $approvedAmount = '',
        string $signingUrl = '',
        Customer $customer = null
    ) {
        $this->paymentId = $paymentId;
        $this->bookPaymentStatus = $bookPaymentStatus;
        $this->approvedAmount = $approvedAmount;
        $this->signingUrl = $signingUrl;
        $this->customer = $customer ?? new Customer();
    }

    /**
     * @see Payment::$paymentId
     * @param string $value
     * @return self
     */
    public function setPaymentId(
        string $value
    ): self {
        $this->paymentId = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setBookPaymentStatus(
        string $value
    ): self {
        $this->bookPaymentStatus = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getBookPaymentStatus(): string
    {
        return $this->bookPaymentStatus;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setApprovedAmount(
        string $value
    ): self {
        $this->approvedAmount = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getApprovedAmount(): string
    {
        return $this->approvedAmount;
    }

    /**
     * @see Payment::$signingUrl
     * @param string $value
     * @return self
     */
    public function setSigningUrl(
        string $value
    ): self {
        $this->signingUrl = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getSigningUrl(): string
    {
        return $this->signingUrl;
    }

    /**
     * @param Customer $value
     * @return self
     */
    public function setCustomer(
        Customer $value
    ): self {
        $this->customer = $value;

        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }
}
