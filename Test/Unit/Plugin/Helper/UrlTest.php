<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Test\Unit\Plugin\Helper;

use PHPUnit\Framework\TestCase;
use Resursbank\Simplified\Plugin\Helper\Url;

class UrlTest extends TestCase
{
    /**
     * Object to test
     *
     * @var Url
     */
    private Url $url;

    /**
     * @inheriDoc
     */
    public function setUp(): void
    {

        $this->url = new Url();
    }

    public function testAfterGetCheckoutRebuildRedirectUrlAddsRequiredText()
    {
        $urlMock = $this->createMock(\Resursbank\Core\Helper\Url::class);
        self::assertEquals(
            "https://website.com/url/structure/#payment",
            $this->url->afterGetCheckoutRebuildRedirectUrl($urlMock, "https://website.com/url/structure")
        );
    }
}
