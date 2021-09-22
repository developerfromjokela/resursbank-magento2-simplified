<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Resursbank\Simplified\Helper\Config;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ConfigTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigInterfaceMock;

    /**
     * @var \Resursbank\Core\Helper\Config|MockObject
     */
    private $coreConfigMock;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @InheritDoc
     */
    public function setUp(): void
    {
        $this->scopeConfigInterfaceMock = $this->createMock(ScopeConfigInterface::class);
        $writerMock = $this->createMock(WriterInterface::class);
        $this->coreConfigMock = $this->createMock(\Resursbank\Core\Helper\Config::class);
        $contextMock = $this->createMock(Context::class);
        $this->config = new Config(
            $this->scopeConfigInterfaceMock,
            $writerMock,
            $this->coreConfigMock,
            $contextMock,
        );
    }

    /**
     * Assert that isActive return the correct value.
     */
    public function testIsActive(): void
    {
        $this->coreConfigMock
            ->method('getFlow')
            ->with('', ScopeInterface::SCOPE_STORES)
            ->willReturn(Config::API_FLOW_OPTION);

        self::assertTrue($this->config->isActive(''));
    }

    /**
     * Assert that isActive returns true on store specific store if enabled.
     */
    public function testIsActiveReturnsTrueForSpecificStore(): void
    {
        $this->coreConfigMock->method('getFlow')
            ->with('en', ScopeInterface::SCOPE_STORES)
            ->willReturn(Config::API_FLOW_OPTION);

        self::assertTrue($this->config->isActive('en'));
    }

    /**
     * Assert that isActive returns false if disabled on specific store.
     */
    public function testIsActiveReturnsFalseForSpecificStore(): void
    {
        $this->coreConfigMock->method('getFlow')
            ->with('se', ScopeInterface::SCOPE_STORES)
            ->willReturn('something else');

        self::assertFalse($this->config->isActive('se'));
    }

    /**
     * Assert that isActive returns true for specific store if disabled on others.
     */
    public function testIsActiveReturnsFalseForSpecificStoreIfEnableOnOther(): void
    {
        $this->coreConfigMock->method('getFlow')->withConsecutive(
            ['en', ScopeInterface::SCOPE_STORES],
            ['se', ScopeInterface::SCOPE_STORES],
        )->willReturnOnConsecutiveCalls(Config::API_FLOW_OPTION, 'something else');

        self::assertTrue($this->config->isActive('en'));
        self::assertFalse($this->config->isActive('se'));
    }

    /**
     * Assert that isWaitingForFraudControl return the correct value.
     */
    public function testIsWaitingForFraudControl(): void
    {
        $this->scopeConfigInterfaceMock->method('isSetFlag')
            ->with('resursbank/advanced/wait_for_fraud_control', ScopeInterface::SCOPE_STORES)
            ->willReturn(true);

        self::assertTrue($this->config->isWaitingForFraudControl(''));
    }

    /**
     * Assert that isWaitingForFraudControl returns true on store specific store if enabled.
     */
    public function testIsWaitingForFraudControlReturnsTrueForSpecificStore(): void
    {
        $this->scopeConfigInterfaceMock->method('isSetFlag')
            ->with('resursbank/advanced/wait_for_fraud_control', ScopeInterface::SCOPE_STORES, 'en')
            ->willReturn(true);

        self::assertTrue($this->config->isWaitingForFraudControl('en'));
    }

    /**
     * Assert that isWaitingForFraudControl returns false if disabled on specific store.
     */
    public function testIsWaitingForFraudControlReturnsFalseForSpecificStore(): void
    {
        $this->scopeConfigInterfaceMock->method('isSetFlag')
            ->with('resursbank/advanced/wait_for_fraud_control', ScopeInterface::SCOPE_STORES, 'se')
            ->willReturn(false);

        self::assertFalse($this->config->isWaitingForFraudControl('se'));
    }

    /**
     * Assert that isWaitingForFraudControl returns true for specific store if disabled on others.
     */
    public function testIsWaitingForFraudControlReturnsFalseForSpecificStoreIfEnableOnOther(): void
    {
        $this->scopeConfigInterfaceMock->method('isSetFlag')->withConsecutive(
            ['resursbank/advanced/wait_for_fraud_control', ScopeInterface::SCOPE_STORES, 'en'],
            ['resursbank/advanced/wait_for_fraud_control', ScopeInterface::SCOPE_STORES, 'se'],
        )->willReturnOnConsecutiveCalls(true, false);

        self::assertTrue($this->config->isWaitingForFraudControl('en'));
        self::assertFalse($this->config->isWaitingForFraudControl('se'));
    }

    /**
     * Assert that isAnnulIfFrozen return the correct value.
     */
    public function tesIsAnnulIfFrozen(): void
    {
        $this->coreConfigMock->method('isSetFlag')
            ->with('resursbank/advanced/annul_if_frozen', ScopeInterface::SCOPE_STORES)
            ->willReturn(true);

        self::assertTrue($this->config->isAnnulIfFrozen(''));
    }

    /**
     * Assert that isAnnulIfFrozen returns true on store specific store if enabled.
     */
    public function tesIsAnnulIfFrozenReturnsTrueForSpecificStore(): void
    {
        $this->coreConfigMock->method('isSetFlag')
            ->with('resursbank/advanced/annul_if_frozen', ScopeInterface::SCOPE_STORES, 'en')
            ->willReturn(true);

        self::assertTrue($this->config->isAnnulIfFrozen('en'));
    }

    /**
     * Assert that isAnnulIfFrozen returns false if disabled on specific store.
     */
    public function tesIsAnnulIfFrozenReturnsFalseForSpecificStore(): void
    {
        $this->coreConfigMock->method('isSetFlag')
            ->with('resursbank/advanced/annul_if_frozen', ScopeInterface::SCOPE_STORE, 'se')
            ->willReturn(false);

        self::assertFalse($this->config->isAnnulIfFrozen('se'));
    }

    /**
     * Assert that isAnnulIfFrozen returns true for specific store if disabled on others.
     */
    public function tesIsAnnulIfFrozenReturnsFalseForSpecificStoreIfEnableOnOther(): void
    {
        $this->coreConfigMock->method('isSetFlag')->withConsecutive(
            ['resursbank/advanced/annul_if_frozen', ScopeInterface::SCOPE_STORES, 'en'],
            ['resursbank/advanced/annul_if_frozen', ScopeInterface::SCOPE_STORES, 'se'],
        )->willReturnOnConsecutiveCalls(true, false);

        self::assertTrue($this->config->isAnnulIfFrozen('en'));
        self::assertFalse($this->config->isAnnulIfFrozen('se'));
    }

    /**
     * Assert that isFinalizeIfBooked return the correct value.
     */
    public function tesIsFinalizeIfBooked(): void
    {
        $this->coreConfigMock->method('isSetFlag')
            ->with('resursbank/advanced/finalize_if_booked', ScopeInterface::SCOPE_STORES)
            ->willReturn(true);

        self::assertTrue($this->config->isFinalizeIfBooked(''));
    }

    /**
     * Assert that isFinalizeIfBooked returns true on store specific store if enabled.
     */
    public function tesIsFinalizeIfBookedReturnsTrueForSpecificStore(): void
    {
        $this->coreConfigMock->method('isSetFlag')
            ->with('resursbank/advanced/finalize_if_booked', ScopeInterface::SCOPE_STORES, 'en')
            ->willReturn(true);

        self::assertTrue($this->config->isFinalizeIfBooked('en'));
    }

    /**
     * Assert that isFinalizeIfBooked returns false if disabled on specific store.
     */
    public function tesIsFinalizeIfBookedReturnsFalseForSpecificStore(): void
    {
        $this->coreConfigMock->method('isSetFlag')
            ->with('resursbank/advanced/finalize_if_booked', ScopeInterface::SCOPE_STORES, 'se')
            ->willReturn(false);

        self::assertFalse($this->config->isFinalizeIfBooked('se'));
    }

    /**
     * Assert that isFinalizeIfBooked returns true for specific store if disabled on others.
     */
    public function tesIsFinalizeIfBookedReturnsFalseForSpecificStoreIfEnableOnOther(): void
    {
        $this->coreConfigMock->method('isSetFlag')->withConsecutive(
            ['resursbank/advanced/finalize_if_booked', ScopeInterface::SCOPE_STORES, 'en'],
            ['resursbank/advanced/finalize_if_booked', ScopeInterface::SCOPE_STORES, 'se'],
        )->willReturnOnConsecutiveCalls(true, false);

        self::assertTrue($this->config->isFinalizeIfBooked('en'));
        self::assertFalse($this->config->isFinalizeIfBooked('se'));
    }
}
