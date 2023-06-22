<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use PHPUnit\Framework\TestCase;
use Resursbank\Simplified\Helper\ValidateGovId;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ValidateGovIdTest extends TestCase
{

    /**
     * @var ValidateGovId
     */
    private ValidateGovId $validateGovId;

    /**
     * @inheriDoc
     */
    public function setUp(): void
    {
        $context = $this->createMock(Context::class);
        $this->validateGovId = new ValidateGovId(
            $context
        );
    }

    /**
     * Assert that validate returns false when no supplying an ID and allowEmpty is false.
     */
    public function testValidateReturnsFalseOnEmptyIdAndAllowEmptyFalse(): void
    {
        self::assertfalse($this->validateGovId->validate('', true, 'SE', false));
    }

    /**
     * Assert that validate returns false when no supplying an ID and allowEmpty is false.
     */
    public function testValidateReturnsTrueOnEmptyIdAndAllowEmptyTrue(): void
    {
        self::assertTrue($this->validateGovId->validate('', true, 'SE', true));
    }

    /**
     * Assert that validate returns false when an invalid country is sent.
     */
    public function testValidateReturnsFalseOnInvalidCountry(): void
    {
        self::assertFalse($this->validateGovId->validate('198001010001', true, 'CH'));
    }

    /**
     * Assert that a valid Swedish OrgId is accepted by validate.
     */
    public function testValidateReturnsTrueOnSwedishOrgId(): void
    {
        self::assertTrue($this->validateGovId->validate('198001010001', true, 'SE'));
    }

    /**
     * Assert that an invalid Swedish OrgId is not accepted by validate.
     */
    public function testValidateReturnsFalseOnNonSwedishOrgId(): void
    {
        self::assertFalse($this->validateGovId->validate('19801010001', true, 'SE'));
    }

    /**
     * Assert that a valid Norwegian OrgId is accepted by validate.
     */
    public function testValidateReturnsTrueOnNorwegianOrgId(): void
    {
        self::assertTrue($this->validateGovId->validate('198001010001', true, 'NO'));
    }

    /**
     * Assert that an invalid Norwegian OrgId is not accepted by validate.
     */
    public function testValidateReturnsFalseOnNonNorwegianOrgId(): void
    {
        self::assertFalse($this->validateGovId->validate('19801010001', true, 'NO'));
    }

    /**
     * Assert that a valid Finnish OrgId is accepted by validate.
     */
    public function testValidateReturnsTrueOnFinnishOrgId(): void
    {
        self::assertTrue($this->validateGovId->validate('1980010-1', true, 'FI'));
    }

    /**
     * Assert that an invalid Finnish OrgId is not accepted by validate.
     */
    public function testValidateReturnsFalseOnNonSFinnishOrgId(): void
    {
        self::assertFalse($this->validateGovId->validate('1980010-12', true, 'FI'));
    }

    /**
     * Assert that a valid Danish OrgId is accepted by validate.
     */
    public function testValidateReturnsTrueOnDanishOrgId(): void
    {
        self::assertTrue($this->validateGovId->validate('200408-3468', true, 'DK'));
    }

    /**
     * Assert that an invalid Danish OrgId is not accepted by validate.
     */
    public function testValidateReturnsFalseOnNonDanishOrgId(): void
    {
        self::assertFalse($this->validateGovId->validate('19801010001', true, 'DK'));
    }

    /**
     * Assert that a valid Swedish Ssn is accepted by validate.
     */
    public function testValidateReturnsTrueOnSwedishSsn(): void
    {
        self::assertTrue($this->validateGovId->validate('198001010001', false, 'SE'));
    }

    /**
     * Assert that an invalid Swedish Ssn is not accepted by validate.
     */
    public function testValidateReturnsFalseOnNonSwedishSsn(): void
    {
        self::assertFalse($this->validateGovId->validate('19801010001', false, 'SE'));
    }

    /**
     * Assert that a valid Norwegian Ssn is accepted by validate.
     */
    public function testValidateReturnsTrueOnNorwegianSsn(): void
    {
        self::assertTrue($this->validateGovId->validate('200408-34685', false, 'NO'));
    }

    /**
     * Assert that an invalid Norwegian Ssn is not accepted by validate.
     */
    public function testValidateReturnsFalseOnNonNorwegianSsn(): void
    {
        self::assertFalse($this->validateGovId->validate('200408-3468', false, 'NO'));
    }

    /**
     * Assert that a valid Finnish Ssn is accepted by validate.
     */
    public function testValidateReturnsTrueOnFinnishSsn(): void
    {
        self::assertTrue($this->validateGovId->validate('010101-100X', false, 'FI'));
    }

    /**
     * Assert that an invalid Finnish Ssn is not accepted by validate.
     */
    public function testValidateReturnsFalseOnNonSFinnishSsn(): void
    {
        self::assertFalse($this->validateGovId->validate('010101-100', false, 'FI'));
    }

    /**
     * Assert that a valid Danish Ssn is accepted by validate.
     */
    public function testValidateReturnsTrueOnDanishSsn(): void
    {
        self::assertTrue($this->validateGovId->validate('200408-3468', false, 'DK'));
    }

    /**
     * Assert that an invalid Danish Ssn is not accepted by validate.
     */
    public function testValidateReturnsFalseOnNonDanishSsn(): void
    {
        self::assertFalse($this->validateGovId->validate('19801010001', false, 'DK'));
    }
}
