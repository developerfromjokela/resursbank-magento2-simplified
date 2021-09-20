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
     * Assert that validate returns false when no supplying an ID and allowEmpty is false
     */
    public function testValidateReturnsFalseOnEmptyIdAndAllowEmptyFalse()
    {
        self::assertfalse($this->validateGovId->validate('', true, 'SE', false));
    }

    /**
     * Assert that validate returns false when no supplying an ID and allowEmpty is false
     */
    public function testValidateReturnsTrueOnEmptyIdAndAllowEmptyTrue()
    {
        self::assertTrue($this->validateGovId->validate('', true, 'SE', true));
    }

    /**
     * Assert that validate returns false when an invalid country is sent
     */
    public function testValidateReturnsFalseOnInvalidCountry()
    {
        self::assertFalse($this->validateGovId->validate('198001010001', true, 'CH'));
    }

    /**
     * Assert that a valid Swedish OrgId is accepted by validate
     */
    public function testValidateReturnsTrueOnSwedishOrgId()
    {
        self::assertTrue($this->validateGovId->validate('198001010001', true, 'SE'));
    }

    /**
     * Assert that an invalid Swedish OrgId is accepted by validate
     */
    public function testValidateReturnsFalseOnNonSwedishOrgId()
    {
        self::assertFalse($this->validateGovId->validate('19801010001', true, 'SE'));
    }

    /**
     * Assert that a valid Norwegian OrgId is accepted by validate
     */
    public function testValidateReturnsTrueOnNorwegianOrgId()
    {
        self::assertTrue($this->validateGovId->validate('198001010001', true, 'NO'));
    }

    /**
     * Assert that an invalid Norwegian OrgId is accepted by validate
     */
    public function testValidateReturnsFalseOnNonNorwegianOrgId()
    {
        self::assertFalse($this->validateGovId->validate('19801010001', true, 'NO'));
    }

    /**
     * Assert that a valid Finnish OrgId is accepted by validate
     */
    public function testValidateReturnsTrueOnFinnishOrgId()
    {
        self::assertTrue($this->validateGovId->validate('1980010-1', true, 'FI'));
    }

    /**
     * Assert that an invalid Finnish OrgId is accepted by validate
     */
    public function testValidateReturnsFalseOnNonSFinnishOrgId()
    {
        self::assertFalse($this->validateGovId->validate('1980010-12', true, 'FI'));
    }

    /**
     * Assert that a valid Danish OrgId is accepted by validate
     */
    public function testValidateReturnsTrueOnDanishOrgId()
    {
        self::assertTrue($this->validateGovId->validate('200408-3468', true, 'DK'));
    }

    /**
     * Assert that an invalid Danish OrgId is accepted by validate
     */
    public function testValidateReturnsFalseOnNonDanishOrgId()
    {
        self::assertFalse($this->validateGovId->validate('19801010001', true, 'DK'));
    }


    /**
     * Assert that a valid Swedish Ssn is accepted by validate
     */
    public function testValidateReturnsTrueOnSwedishSsn()
    {
        self::assertTrue($this->validateGovId->validate('198001010001', false, 'SE'));
    }

    /**
     * Assert that an invalid Swedish Ssn is accepted by validate
     */
    public function testValidateReturnsFalseOnNonSwedishSsn()
    {
        self::assertFalse($this->validateGovId->validate('19801010001', false, 'SE'));
    }

    /**
     * Assert that a valid Norwegian Ssn is accepted by validate
     */
    public function testValidateReturnsTrueOnNorwegianSsn()
    {
        self::assertTrue($this->validateGovId->validate('200408-34685', false, 'NO'));
    }

    /**
     * Assert that an invalid Norwegian Ssn is accepted by validate
     */
    public function testValidateReturnsFalseOnNonNorwegianSsn()
    {
        self::assertFalse($this->validateGovId->validate('200408-3468', false, 'NO'));
    }

    /**
     * Assert that a valid Finnish Ssn is accepted by validate
     */
    public function testValidateReturnsTrueOnFinnishSsn()
    {
        self::assertTrue($this->validateGovId->validate('010101-100X', false, 'FI'));
    }

    /**
     * Assert that an invalid Finnish Ssn is accepted by validate
     */
    public function testValidateReturnsFalseOnNonSFinnishSsn()
    {
        self::assertFalse($this->validateGovId->validate('010101-100', false, 'FI'));
    }

    /**
     * Assert that a valid Danish Ssn is accepted by validate
     */
    public function testValidateReturnsTrueOnDanishSsn()
    {
        self::assertTrue($this->validateGovId->validate('200408-3468', false, 'DK'));
    }

    /**
     * Assert that an invalid Danish Ssn is accepted by validate
     */
    public function testValidateReturnsFalseOnNonDanishSsn()
    {
        self::assertFalse($this->validateGovId->validate('19801010001', false, 'DK'));
    }
}
