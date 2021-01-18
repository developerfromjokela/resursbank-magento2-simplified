<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Test\Unit\Controller\Checkout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use Resursbank\Simplified\Exception\MissingRequestParameterException;
use Resursbank\Simplified\Controller\Checkout\FetchAddress;
use \Magento\Framework\App\RequestInterface;

class FetchAddressTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var FetchAddress
     */
    private $controller;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->request = $this->createMock(RequestInterface::class);

        $this->controller = $this->objectManager
            ->getObject(FetchAddress::class, ['request' => $this->request]);
    }

    /**
     * Test that the isCompany method resolves 'true' from the HTTP request as
     * a bool (true).
     */
    public function testIsCompanyResolvesTrue(): void
    {
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('is_company')
            ->willReturn('true');

        try {
            static::assertTrue(
                $this->getIsCompanyMethod()->invoke($this->controller)
            );
        } catch (ReflectionException $e) {
            static::fail('Failed to mock isCompany: ' . $e->getMessage());
        }
    }

    /**
     * Test that the isCompany method resolves 'false' from the HTTP request as
     * a bool (false).
     */
    public function testIsCompanyResolvesFalse(): void
    {
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('is_company')
            ->willReturn('false');

        try {
            static::assertFalse(
                $this->getIsCompanyMethod()->invoke($this->controller)
            );
        } catch (ReflectionException $e) {
            static::fail('Failed to mock isCompany: ' . $e->getMessage());
        }
    }

    /**
     * Test that the isCompany method throws an instance of
     * MissingRequestParameterException if the 'is_company' parameter is absent
     * from the HTTP request.
     */
    public function testIsCompanyThrowsWithoutRequestParam(): void
    {
        $this->expectException(MissingRequestParameterException::class);

        try {
            $this->getIsCompanyMethod()->invoke($this->controller);
        } catch (ReflectionException $e) {
            static::fail('Failed to mock isCompany: ' . $e->getMessage());
        }
    }

    /**
     * Test that the isCompany method throws an instance of
     * MissingRequestParameterException if the 'is_company' parameter is set but
     * has a value other than 'true' or 'false'.
     */
    public function testIsCompanyThrowsWithFaultyRequestParamData(): void
    {
        $this->expectException(MissingRequestParameterException::class);

        $this->request->expects(static::once())
            ->method('getParam')
            ->with('is_company')
            ->willReturn(['that', 'does', 'not', 'take', 'wooden', 'nickels']);

        try {
            $this->getIsCompanyMethod()->invoke($this->controller);
        } catch (ReflectionException $e) {
            static::fail('Failed to mock isCompany: ' . $e->getMessage());
        }
    }

    /**
     * Test that the isCompany method returns boolean with value true directly
     * from the HTTP request.
     */
    public function testIsCompanyReturnsTrue(): void
    {
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('is_company')
            ->willReturn(true);

        try {
            static::assertTrue(
                $this->getIsCompanyMethod()->invoke($this->controller)
            );
        } catch (ReflectionException $e) {
            static::fail('Failed to mock isCompany: ' . $e->getMessage());
        }
    }

    /**
     * Test that the isCompany method returns boolean with value false directly
     * from the HTTP request.
     */
    public function testIsCompanyReturnsFalse(): void
    {
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('is_company')
            ->willReturn(false);

        try {
            static::assertFalse(
                $this->getIsCompanyMethod()->invoke($this->controller)
            );
        } catch (ReflectionException $e) {
            static::fail('Failed to mock isCompany: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve accessible isCompany method mock.
     *
     * @return ReflectionMethod
     */
    private function getIsCompanyMethod(): ReflectionMethod
    {
        $obj = new ReflectionObject($this->controller);
        $method = $obj->getMethod('isCompany');
        $method->setAccessible(true);

        return $method;
    }
}
