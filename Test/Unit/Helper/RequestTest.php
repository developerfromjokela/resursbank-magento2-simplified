<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Test\Unit\Helper;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Exception\MissingRequestParameterException;
use Resursbank\Simplified\Helper\Request;
use Resursbank\Simplified\Helper\ValidateCard;
use Resursbank\Simplified\Helper\ValidateGovernmentId;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class RequestTest extends TestCase
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Request
     */
    private $requestHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->request = $this->createMock(RequestInterface::class);

        $this->requestHelper = $objectManager
            ->getObject(
                Request::class,
                [
                    'request' => $this->request,
                    'validateGovernmentId' => $objectManager->getObject(
                        ValidateGovernmentId::class
                    ),
                    'validateCard' => $objectManager->getObject(
                        ValidateCard::class
                    )
                ]
            );
    }

    /**
     * Test that the isCompany method resolves HTTP parameter 'is_company' with
     * value 'true' (string) as true (bool).
     *
     * @throws MissingRequestParameterException
     */
    public function testIsCompanyResolvesStringTrueAsBool(): void
    {
        $this->request->expects(static::once())
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
        $this->request->expects(static::once())
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

        $this->request->expects(static::once())
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
        $this->request->expects(static::once())
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
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('is_company')
            ->willReturn(false);

        static::assertFalse($this->requestHelper->isCompany());
    }

    /**
     * Test that getGovId method returns valid private citizen (natural) value.
     *
     * @throws MissingRequestParameterException
     * @throws InvalidDataException
     */
    public function testGetGovIdReturnsValidNatural(): void
    {
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('gov_id')
            ->willReturn('198001010001');

        static::assertSame(
            '198001010001',
            $this->requestHelper->getGovId(false)
        );
    }

    /**
     * Test that getGovId method returns valid company (legal) value.
     *
     * @throws MissingRequestParameterException
     * @throws InvalidDataException
     */
    public function testGetGovIdReturnsValidLegal(): void
    {
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('gov_id')
            ->willReturn('169468958195');

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
     * @throws InvalidDataException
     */
    public function testGetGovIdAcceptsNaturalAsLegal(): void
    {
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('gov_id')
            ->willReturn('198001010001');

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
     * @throws InvalidDataException
     */
    public function testGetGovIdThrowsOnWrongType(): void
    {
        $this->expectException(MissingRequestParameterException::class);

        $this->request->expects(static::once())
            ->method('getParam')
            ->with('gov_id')
            ->willReturn(true);

        $this->requestHelper->getGovId(false);
    }

    /**
     * Test that getGovId method throws an instance of
     * MissingRequestParameterException if the 'gov_id' parameter is missing.
     *
     * @throws InvalidDataException
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
     * @throws MissingRequestParameterException
     */
    public function testGetGovIdThrowsWithInvalidNaturalSsn(): void
    {
        $this->expectException(InvalidDataException::class);

        $this->request->expects(static::once())
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
     * @throws MissingRequestParameterException
     */
    public function testGetGovIdThrowsWithInvalidLegalSsn(): void
    {
        $this->expectException(InvalidDataException::class);

        $this->request->expects(static::once())
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
     * @throws InvalidDataException
     */
    public function testGetContactGovIdReturnsValidValue(): void
    {
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('contact_gov_id')
            ->willReturn('198001010001');

        static::assertSame(
            '198001010001',
            $this->requestHelper->getContactGovId()
        );
    }

    /**
     * Test that getContactGovId method throws an instance of
     * InvalidDataException when supplied a company (LEGAL) SSN.
     *
     * @throws MissingRequestParameterException
     */
    public function testGetContactGovIdRejectsLegal(): void
    {
        $this->expectException(InvalidDataException::class);

        $this->request->expects(static::once())
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
     * @throws InvalidDataException
     */
    public function testGetContactGovIdThrowsOnWrongType(): void
    {
        $this->expectException(MissingRequestParameterException::class);

        $this->request->expects(static::once())
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
     * @throws InvalidDataException
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
     * @throws MissingRequestParameterException
     */
    public function testGetContactGovIdThrowsWithInvalidNaturalSsn(): void
    {
        $this->expectException(InvalidDataException::class);

        $this->request->expects(static::once())
            ->method('getParam')
            ->with('contact_gov_id')
            ->willReturn('19441131231');

        $this->requestHelper->getContactGovId();
    }

    /**
     * Test that getCardNumber method returns valid card number.
     *
     * @throws InvalidDataException
     */
    public function testGetCardNumberReturnsValidValue(): void
    {
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('card_number')
            ->willReturn('9000 0000 0001 0000');

        static::assertSame(
            '9000 0000 0001 0000',
            $this->requestHelper->getCardNumber()
        );
    }

    /**
     * Test that getCardNumber method returns NULL when the request parameter
     * 'card_number' is set to a value other than a string.
     *
     * @throws InvalidDataException
     */
    public function testGetCardNumberReturnsNullWithWrongType(): void
    {
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('card_number')
            ->willReturn([123]);

        static::assertNull($this->requestHelper->getCardNumber());
    }

    /**
     * Test that getCardNumber method returns NULL when the request parameter
     * 'card_number' is unassigned.
     *
     * @throws InvalidDataException
     */
    public function testGetCardNumberReturnsNullWithoutData(): void
    {
        static::assertNull($this->requestHelper->getCardNumber());
    }

    /**
     * Test that getCardNumber method throws an instance of InvalidDataException
     * when the request parameter 'card_number' is assigned an empty string.
     */
    public function testGetCardNumberThrowsWithEmptyString(): void
    {
        $this->expectException(InvalidDataException::class);

        $this->request->expects(static::once())
            ->method('getParam')
            ->with('card_number')
            ->willReturn('');

        $this->requestHelper->getCardNumber();
    }

    /**
     * Test that getCardNumber method throws an instance of InvalidDataException
     * when the request parameter 'card_number' is assigned an invalid value.
     */
    public function testGetCardNumberThrowsWithInvalidData(): void
    {
        $this->expectException(InvalidDataException::class);

        $this->request->expects(static::once())
            ->method('getParam')
            ->with('card_number')
            ->willReturn('that does not take wooden nickels');

        $this->requestHelper->getCardNumber();
    }

    /**
     * Test that getCardAmount method returns valid card amount.
     *
     * @throws InvalidDataException
     */
    public function testGetCardAmountReturnsFloat(): void
    {
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('card_amount')
            ->willReturn(155.76);

        static::assertSame(155.76, $this->requestHelper->getCardAmount());
    }

    /**
     * Test that getCardAmount method parses numeric string value and returns
     * float.
     *
     * @throws InvalidDataException
     */
    public function testGetCardAmountReturnsParsedStringAsFloat(): void
    {
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('card_amount')
            ->willReturn('88');

        static::assertSame(88.0, $this->requestHelper->getCardAmount());
    }

    /**
     * Test that getCardAmount method throws and instance of
     * InvalidDataException when the 'card_amount' parameter is assigned a none
     * numeric value.
     */
    public function testGetCardAmountThrowsWithNoneNumericValue(): void
    {
        $this->expectException(InvalidDataException::class);

        $this->request->expects(static::once())
            ->method('getParam')
            ->with('card_amount')
            ->willReturn(true);

        $this->requestHelper->getCardAmount();
    }

    /**
     * Test that getCardAmount method throws and instance of
     * InvalidDataException when the request parameter 'card_amount' is set to a
     * value other than a string or number.
     */
    public function testGetCardAmountReturnsNullWithWrongType(): void
    {
        $this->expectException(InvalidDataException::class);

        $this->request->expects(static::once())
            ->method('getParam')
            ->with('card_amount')
            ->willReturn([123]);

        $this->requestHelper->getCardAmount();
    }

    /**
     * Test that getCardAmount method throws an instance of InvalidDataException
     * when the request parameter 'card_amount' is assigned an empty string.
     */
    public function testGetCardAmountThrowsWithEmptyString(): void
    {
        $this->expectException(InvalidDataException::class);

        $this->request->expects(static::once())
            ->method('getParam')
            ->with('card_amount')
            ->willReturn('');

        $this->requestHelper->getCardAmount();
    }

    /**
     * Test that getCardAmount method returns null when the 'card_amount'
     * request parameter is unassigned.
     *
     * @throws InvalidDataException
     */
    public function testGetCardAmountReturnsNullWithoutData(): void
    {
        static::assertNull($this->requestHelper->getCardAmount());
    }

    /**
     * Test that getCardAmount method returns 0.0 instead of NULL.
     *
     * @throws InvalidDataException
     */
    public function testGetCardAmountReturns0(): void
    {
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('card_amount')
            ->willReturn(0.0);

        static::assertSame(0.0, $this->requestHelper->getCardAmount());
    }
}
