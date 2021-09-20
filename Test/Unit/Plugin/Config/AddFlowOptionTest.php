<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Test\Unit\Plugin\Config;

use PHPUnit\Framework\TestCase;
use Resursbank\Core\Model\Config\Source\Flow;
use Resursbank\Simplified\Helper\Config;
use Resursbank\Simplified\Plugin\Config\AddFlowOption;

class AddFlowOptionTest extends TestCase
{

    /**
     * @var AddFlowOption
     */
    private $addFlowOption;

    /**
     * @inheriDoc
     */
    public function setUp(): void
    {

        $this->addFlowOption = new AddFlowOption();
    }

    public function testAfterToArrayAddsValueToArray()
    {
        $inData = [
            "key" => "value"
        ];
        $expected = [
            "key" => "value",
            Config::API_FLOW_OPTION => __(
                'Two step Magento Checkout with Resurs payment methods'
            ),
        ];
        $flowMock = $this->createMock(Flow::class);
        self::assertEquals($expected, $this->addFlowOption->afterToArray($flowMock, $inData));
    }
}
