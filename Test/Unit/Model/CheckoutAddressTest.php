<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Exception\ApiDataException;
use Resursbank\Simplified\Model\CheckoutAddress;

class CheckoutAddressTest extends TestCase
{

    /**
     * @var CheckoutAddress
     */
    private CheckoutAddress $checkoutAddress;

    /**
     * @var string[]
     */
    private $checkoutOutAddressData = [
        "firstname" => "Göran",
        "lastname" => "Göransson",
        "city" => "Ankeborg",
        "postcode" => "125  97",
        "country" => "SE",
        "street0" => "Street 1",
        "street1" => "",
        "company" => "Some company",
        "telephone" => "0720456789",
    ];

    /**
     * @inheriDoc
     */
    public function setUp(): void
    {
        $this->checkoutAddress = new CheckoutAddress(
            "Göran",
            "Göransson",
            "Ankeborg",
            "125 97",
            "SE",
            "Street 1",
            '',
            "Some company",
            "0720456789"
        );
    }

    /**
     * Assert that toArray returns a correct array
     */
    public function testToArrayMatchesExpectedValues()
    {
        self::assertEquals($this->checkoutOutAddressData, $this->checkoutAddress->toArray());
    }

    /**
     * Assert that getCountry returns a correct value
     */
    public function testGetCountryReturnsCorrectValue()
    {
        self::assertEquals($this->checkoutOutAddressData['country'], $this->checkoutAddress->getCountry());
    }

    /**
     * Assert that getPostcode returns a correct value
     */
    public function testGetPostCodeReturnsCorrectValue()
    {
        self::assertEquals($this->checkoutOutAddressData['postcode'], $this->checkoutAddress->getPostcode());
    }

    /**
     * Assert that setCountry sets a new value
     * @throws ApiDataException
     */
    public function testSetCountrySetsNewValue()
    {
        $this->checkoutAddress->setCountry('NO');
        self::assertEquals('NO', $this->checkoutAddress->getCountry());
    }

    /**
     * Assert that setCountry throws exception on invalid country
     * @throws ApiDataException
     */
    public function testSetCountryThrowsExceptionOnInvalidCountry()
    {
        $this->expectException(ApiDataException::class);
        $this->expectErrorMessage("US is not a valid country.");
        $this->checkoutAddress->setCountry('US');
    }

    /**
     * Assert that setPostcode sets a new value
     */
    public function testSetPostCodeSetsNewValue()
    {
        $this->checkoutAddress->setPostcode('654987','NO');
        self::assertEquals('654987', $this->checkoutAddress->getPostcode());
    }

    /**
     * Assert that setPostcode reformat invalid Swedish Postcode
     */
    public function testSetPostCodeThrowsExceptionOnInvalidSwedishPostcode()
    {
        $this->checkoutAddress->setPostcode('12345','SE');
        self::assertEquals('123 45', $this->checkoutAddress->getPostcode());
    }
}
