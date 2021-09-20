<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Test\Unit\Plugin\Helper;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Helper\Api as CoreApi;
use Resursbank\Core\Helper\Version;
use Resursbank\Simplified\Plugin\Helper\Api;

class ApiTest extends TestCase
{
    /**
     * @var Version|MockObject
     */
    private $version;

    /**
     * @var Api
     */
    private Api $api;

    /**
     * Main set up method
     */
    public function setUp(): void
    {
        $this->version = $this->createMock(Version::class);
        $this->api = new Api($this->version);
    }

    /**
     * Assert that afterGetUserAgent return the correct string
     */
    public function testAfterGetUserAgent()
    {
        $this->version
            ->expects(self::once())
            ->method("getComposerVersion")
            ->with('Resursbank_Simplified')
            ->willReturn("1.0.0");
        $coreApiMock = $this->createMock(CoreApi::class);
        $this->assertEquals(
            'Magento 2 | Resursbank_Core 1.0.0 | Resursbank_Simplified 1.0.0',
            $this->api->afterGetUserAgent($coreApiMock, 'Magento 2 | Resursbank_Core 1.0.0')
        );
    }
}
