<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Model\Api;

use function strtoupper;

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
     * @var float
     */
    private $approvedAmount;

    /**
     * Gateway URL for payment client will be redirected to when placing order.
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
     * @param float $approvedAmount
     * @param string $signingUrl
     * @param Customer|null $customer
     */
    public function __construct(
        string $paymentId = '',
        string $bookPaymentStatus = '',
        float $approvedAmount = 0.0,
        string $signingUrl = '',
        Customer $customer = null
    ) {
        $this->setPaymentId($paymentId)
            ->setBookPaymentStatus($bookPaymentStatus)
            ->setApprovedAmount($approvedAmount)
            ->setSigningUrl($signingUrl)
            ->setCustomer($customer ?? new Customer());
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
     * @see Payment::$paymentId
     * @return string
     */
    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    /**
     * @see Payment::$bookPaymentStatus
     * @param string $value
     * @return self
     */
    public function setBookPaymentStatus(
        string $value
    ): self {
        $this->bookPaymentStatus = strtoupper($value);

        return $this;
    }

    /**
     * @see Payment::$bookPaymentStatus
     * @return string
     */
    public function getBookPaymentStatus(): string
    {
        return $this->bookPaymentStatus;
    }

    /**
     * @see Payment::$approvedAmount
     * @param float $value
     * @return self
     */
    public function setApprovedAmount(
        float $value
    ): self {
        $this->approvedAmount = $value;

        return $this;
    }

    /**
     * @see Payment::$approvedAmount
     * @return float
     * @noinspection PhpUnused
     */
    public function getApprovedAmount(): float
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
     * @see Payment::$signingUrl
     * @return string
     */
    public function getSigningUrl(): string
    {
        return $this->signingUrl;
    }

    /**
     * @see Payment::$customer
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
     * @see Payment::$customer
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }
}
