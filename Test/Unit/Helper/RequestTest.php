<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Exception\MissingRequestParameterException;
use Resursbank\Core\Helper\Config as CoreConfig;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Request;
use Resursbank\Simplified\Helper\ValidateGovId;
use Resursbank\Simplified\Helper\ValidatePhoneNumber;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RequestTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Request
     */
    private Request $requestHelper;

    /**
     * @var ValidateGovId|MockObject
     */
    private $validateGovIdMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $contextMock = $this->createMock(Context::class);
        $resultFactoryMock = $this->createMock(ResultFactory::class);
        $logMock = $this->createMock(Log::class);
        $this->validateGovIdMock = $this->createMock(ValidateGovId::class);
        $validatePhoneNumberMock = $this->createMock(ValidatePhoneNumber::class);
        $coreConfigMock = $this->createMock(CoreConfig::class);
        $storeManagerInterfaceMock = $this->createMock(StoreManagerInterface::class);
        $storeMock = $this->createMock(StoreInterface::class);

        $this->requestHelper =  new Request(
            $contextMock,
            $resultFactoryMock,
            $logMock,
            $this->requestMock,
            $this->validateGovIdMock,
            $validatePhoneNumberMock,
            $coreConfigMock,
            $storeManagerInterfaceMock
        );

        $storeManagerInterfaceMock->method('getStore')->willReturn($storeMock);

        $storeMock->method('getCode')->willReturn('SE');

        $coreConfigMock->method('getDefaultCountry')->with('SE')->willReturn('SE');
    }

    /**
     * Test that the isCompany method resolves HTTP parameter 'is_company' with
     * value 'true' (string) as true (bool).
     *
     * @throws MissingRequestParameterException
     */
    public function testIsCompanyResolvesStringTrueAsBool(): void
    {
        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('is_company')
            ->willReturn('true');

        static::assertTrue($this->requestHelper->isCompany());
    }

    /**
     * Test that the isCompany method resolves HTTP parameter 'is_company' with
     * value 'false' (string) as false (bool).
     *
     * @throws MissingRequestParameterException
     */
    public function testIsCompanyResolvesStringFalseAsBool(): void
    {
        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('is_company')
            ->willReturn('false');

        static::assertFalse($this->requestHelper->isCompany());
    }

    /**
     * Test that the isCompany method throws an instance of
     * MissingRequestParameterException if HTTP parameter 'is_company' is absent
     * from the request.
     */
    public function testIsCompanyThrowsWithoutRequestParam(): void
    {
        $this->expectException(MissingRequestParameterException::class);
        $this->requestHelper->isCompany();
    }

    /**
     * Test that the isCompany method throws an instance of
     * MissingRequestParameterException if the 'is_company' parameter is set but
     * has a value other than 'true', 'false', true or false.
     */
    public function testIsCompanyThrowsWithFaultyRequestParameterValue(): void
    {
        $this->expectException(MissingRequestParameterException::class);

        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('is_company')
            ->willReturn(['that', 'does', 'not', 'take', 'wooden', 'nickels']);

        $this->requestHelper->isCompany();
    }

    /**
     * Test that the isCompany method resolve value true (bool) directly from
     * HTTP request parameter 'is_company'.
     *
     * @throws MissingRequestParameterException
     */
    public function testIsCompanyReturnsTrue(): void
    {
        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('is_company')
            ->willReturn(true);

        static::assertTrue($this->requestHelper->isCompany());
    }

    /**
     * Test that the isCompany method resolve value false (bool) directly from
     * HTTP request parameter 'is_company'.
     *
     * @throws MissingRequestParameterException
     */
    public function testIsCompanyReturnsFalse(): void
    {
        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('is_company')
            ->willReturn(false);

        static::assertFalse($this->requestHelper->isCompany());
    }

    /**
     * Test that getGovId method returns valid private citizen (natural) value.
     *
     * @throws MissingRequestParameterException
     * @throws InvalidDataException|NoSuchEntityException
     */
    public function testGetGovIdReturnsValidNatural(): void
    {
        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('gov_id')
            ->willReturn('198001010001');

        /** @phpstan-ignore-next-line Undefined method. */
        $this->validateGovIdMock->expects(self::once())
            ->method('validate')
            ->with('198001010001', false, 'SE')
            ->willReturn(true);

        static::assertSame(
            '198001010001',
            $this->requestHelper->getGovId(false)
        );
    }

    /**
     * Test that getGovId method returns valid company (legal) value.
     *
     * @throws MissingRequestParameterException
     * @throws InvalidDataException|NoSuchEntityException
     */
    public function testGetGovIdReturnsValidLegal(): void
    {
        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('gov_id')
            ->willReturn('169468958195');

        /** @phpstan-ignore-next-line Undefined method. */
        $this->validateGovIdMock->expects(self::once())
            ->method('validate')
            ->with('169468958195', true, 'SE')
            ->willReturn(true);

        static::assertSame(
            '169468958195',
            $this->requestHelper->getGovId(true)
        );
    }

    /**
     * Test that getGovId method validates a private citizen (NATURAL) value for
     * a company (LEGAL). A company SSN may be constructed as a private citizen
     * SSN in Sweden.
     *
     * @throws MissingRequestParameterException
     * @throws InvalidDataException|NoSuchEntityException
     */
    public function testGetGovIdAcceptsNaturalAsLegal(): void
    {
        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('gov_id')
            ->willReturn('198001010001');

        /** @phpstan-ignore-next-line Undefined method. */
        $this->validateGovIdMock->expects(self::once())
            ->method('validate')
            ->with('198001010001', true, 'SE')
            ->willReturn(true);

        static::assertSame(
            '198001010001',
            $this->requestHelper->getGovId(true)
        );
    }

    /**
     * Test that getGovId method throws an instance of
     * MissingRequestParameterException if the parameter is set but is not a
     * string.
     *
     * @throws InvalidDataException|NoSuchEntityException
     */
    public function testGetGovIdThrowsOnWrongType(): void
    {
        $this->expectException(MissingRequestParameterException::class);

        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('gov_id')
            ->willReturn(true);

        $this->requestHelper->getGovId(false);
    }

    /**
     * Test that getGovId method throws an instance of
     * MissingRequestParameterException if the 'gov_id' parameter is missing.
     *
     * @throws InvalidDataException|NoSuchEntityException
     */
    public function testGetGovIdThrowsWithoutValue(): void
    {
        $this->expectException(MissingRequestParameterException::class);
        $this->requestHelper->getGovId(false);
    }

    /**
     * Test that getGovId method throws an instance of
     * InvalidDataException if the 'gov_id' parameter contains an inaccurate
     * SSN for private citizen (NATURAL).
     *
     * @throws MissingRequestParameterException|NoSuchEntityException
     */
    public function testGetGovIdThrowsWithInvalidNaturalSsn(): void
    {
        $this->expectException(InvalidDataException::class);

        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('gov_id')
            ->willReturn('19441131231');

        $this->requestHelper->getGovId(false);
    }

    /**
     * Test that getGovId method throws an instance of
     * InvalidDataException if the 'gov_id' parameter contains an inaccurate
     * SSN for company (LEGAL).
     *
     * @throws MissingRequestParameterException|NoSuchEntityException
     */
    public function testGetGovIdThrowsWithInvalidLegalSsn(): void
    {
        $this->expectException(InvalidDataException::class);

        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('gov_id')
            ->willReturn('54667545645');

        $this->requestHelper->getGovId(true);
    }

    /**
     * Test that getContactGovId method returns valid private citizen (NATURAL)
     * value.
     *
     * @throws MissingRequestParameterException
     * @throws InvalidDataException|NoSuchEntityException
     */
    public function testGetContactGovIdReturnsValidValue(): void
    {
        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('contact_gov_id')
            ->willReturn('198001010001');

        /** @phpstan-ignore-next-line Undefined method. */
        $this->validateGovIdMock->expects(self::once())
            ->method('validate')
            ->with('198001010001', false, 'SE')
            ->willReturn(true);

        static::assertSame(
            '198001010001',
            $this->requestHelper->getContactGovId()
        );
    }

    /**
     * Test that getContactGovId method throws an instance of
     * InvalidDataException when supplied a company (LEGAL) SSN.
     *
     * @throws MissingRequestParameterException|NoSuchEntityException
     */
    public function testGetContactGovIdRejectsLegal(): void
    {
        $this->expectException(InvalidDataException::class);

        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('contact_gov_id')
            ->willReturn('166997368573');

        $this->requestHelper->getContactGovId();
    }

    /**
     * Test that getContactGovId method throws an instance of
     * MissingRequestParameterException if the parameter is set but is not a
     * string.
     *
     * @throws InvalidDataException|NoSuchEntityException
     */
    public function testGetContactGovIdThrowsOnWrongType(): void
    {
        $this->expectException(MissingRequestParameterException::class);

        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('contact_gov_id')
            ->willReturn(true);

        $this->requestHelper->getContactGovId();
    }

    /**
     * Test that getContactGovId method throws an instance of
     * MissingRequestParameterException if the 'contact_gov_id' parameter is
     * missing.
     *
     * @throws InvalidDataException|NoSuchEntityException
     */
    public function testGetContactGovIdThrowsWithoutValue(): void
    {
        $this->expectException(MissingRequestParameterException::class);
        $this->requestHelper->getContactGovId();
    }

    /**
     * Test that getContactGovId method throws an instance of
     * InvalidDataException if the 'contact_gov_id' parameter contains an
     * inaccurate SSN for private citizen (NATURAL).
     *
     * @throws MissingRequestParameterException|NoSuchEntityException
     */
    public function testGetContactGovIdThrowsWithInvalidNaturalSsn(): void
    {
        $this->expectException(InvalidDataException::class);

        /** @phpstan-ignore-next-line Undefined method. */
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('contact_gov_id')
            ->willReturn('19441131231');

        $this->requestHelper->getContactGovId();
    }
}
