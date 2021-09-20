<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Exception\ApiDataException;
use Resursbank\Core\Helper\Api;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\Config;
use Resursbank\Simplified\Helper\Address;
use Resursbank\Simplified\Model\CheckoutAddress;

class AddressTest extends TestCase
{

    /**
     * @var Address
     */
    private Address $address;

    /**
     * @var \Resursbank\Core\Model\Api\Address
     */
    private \Resursbank\Core\Model\Api\Address $testApiAddress;

    /**
     * @inheriDoc
     */
    public function setUp():void
    {
        $contextMock = $this->createMock(Context::class);
        $credentialsMock = $this->createMock(Credentials::class);
        $coreApiMock = $this->createMock(Api::class);
        $storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $configMock = $this->createMock(Config::class);
        $this->address = new Address(
            $contextMock,
            $credentialsMock,
            $coreApiMock,
            $storeManagerMock,
            $configMock
        );

        $this->testApiAddress = new \Resursbank\Core\Model\Api\Address(
            false,
            'Göran Göransson',
           'Göran',
            'Göransson',
            'Storagatan 1B',
            '',
            'Ankeborg',
            '213 45',
            'SE',
            '072012345678'
        );
    }

    /**
     * Assert that toCheckoutAddress returns the correct Class
     *
     * @throws ApiDataException
     */
    public function testToCheckoutAddressReturnsCorrectClass()
    {
        self::assertInstanceOf(CheckoutAddress ::class, $this->address->toCheckoutAddress($this->testApiAddress));
    }

    /**
     * Assert that toCheckoutAddress returns the correct Class
     *
     * @throws ApiDataException
     */
    public function testToCheckoutAddressTrowsExceptionOnInvalidCountry()
    {
        $this->expectException(ApiDataException::class);
        $this->expectErrorMessage('EN is not a valid country');
        $this->testApiAddress->setCountry("EN");
        self::assertInstanceOf(CheckoutAddress ::class, $this->address->toCheckoutAddress($this->testApiAddress));
    }

    /**
     * Assert that getCustomerType returns correct values
     */
    public function testGetCustomerTypeReturnsCorrectValues()
    {
        self::assertEquals(Address::CUSTOMER_TYPE_COMPANY,$this->address->getCustomerType(true));
        self::assertEquals(Address::CUSTOMER_TYPE_PRIVATE,$this->address->getCustomerType(false));
    }
}
