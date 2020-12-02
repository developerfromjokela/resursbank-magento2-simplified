<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Model\Api;

use Resursbank\Simplified\Model\Api\Address;

/**
 * Customer information that comes back after creating a payment using the API.
 */
class Customer
{
    /**
     * @var string
     */
    private $governmentId;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $email;

    /**
     * Customer type (company/private person).
     *
     * @var string
     */
    private $type;

    /**
     * @var Address
     */
    private $address;

    /**
     * @param string $governmentId
     * @param string $phone
     * @param string $email
     * @param string $type
     * @param Address|null $address
     */
    public function __construct(
        string $governmentId = '',
        string $phone = '',
        string $email = '',
        string $type = '',
        Address $address = null
    ) {
        $this->governmentId = $governmentId;
        $this->phone = $phone;
        $this->email = $email;
        $this->type = $type;
        $this->address = $address ?? new Address();
    }

    /**
     * @param string $value
     * @return self
     */
    public function setGovernmentId(
        string $value
    ): self {
        $this->governmentId = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getGovernmentId(): string
    {
        return $this->governmentId;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setPhone(
        string $value
    ): self {
        $this->phone = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setEmail(
        string $value
    ): self {
        $this->email = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @see Customer::$type
     * @param string $value
     * @return self
     */
    public function setType(
        string $value
    ): self {
        $this->type = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param Address $value
     * @return self
     */
    public function setAddress(
        Address $value
    ): self {
        $this->address = $value;

        return $this;
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }
}
