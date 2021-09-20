<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Resursbank\Simplified\Helper\ValidatePhoneNumber;

/**
 * @covers \Resursbank\Simplified\Helper\ValidatePhoneNumber
 */
class ValidatePhoneNumberTest extends TestCase
{

    /**
     * @var ValidatePhoneNumber
     */
    private ValidatePhoneNumber $validatePhoneNumber;

    /**
     * @inheriDoc
     */
    public function setUp(): void
    {
        $context = $this->createMock(Context::class);
        $this->validatePhoneNumber = new ValidatePhoneNumber($context);
    }

    /**
     * Assert that the validation of a valid norwegian phone number returns true
     */
    public function testNorwayReturnsTrueOnValidPhoneNumbers()
    {
        self::assertTrue($this->validatePhoneNumber->norway('0047-32 45 78 97'));
        self::assertTrue($this->validatePhoneNumber->norway('004761 45 78 97'));
        self::assertTrue($this->validatePhoneNumber->norway('004732457897'));
        self::assertTrue($this->validatePhoneNumber->norway('32 45 78 97'));
        self::assertTrue($this->validatePhoneNumber->norway('61 45 78 97'));
        self::assertTrue($this->validatePhoneNumber->norway('32457897'));
        self::assertTrue($this->validatePhoneNumber->norway('+47-32 45 78 97'));
        self::assertTrue($this->validatePhoneNumber->norway('+4761 45 78 97'));
        self::assertTrue($this->validatePhoneNumber->norway('+4732457897'));
    }

    /**
     * Assert that the validation of an invalid norwegian phone number returns false
     */
    public function testNorwayReturnsFalseOnInvalidPhoneNumbers()
    {
        self::assertFalse($this->validatePhoneNumber->norway('12 45 78 97'));
        self::assertFalse($this->validatePhoneNumber->norway('004711 45 78 97'));
        self::assertFalse($this->validatePhoneNumber->norway('04732457897'));
        self::assertFalse($this->validatePhoneNumber->norway('0004792457897'));
        self::assertFalse($this->validatePhoneNumber->norway('+4711 45 78 97'));
        self::assertFalse($this->validatePhoneNumber->norway('4792457897'));
    }
}
