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
    private AddFlowOption $addFlowOption;

    /**
     * @inheriDoc
     */
    public function setUp(): void
    {

        $this->addFlowOption = new AddFlowOption();
    }

    /**
     * Assert that the Plugin adds the required field and value to array.
     */
    public function testAfterToArrayAddsValueToArray(): void
    {
        $inData = [
            'key' => __('value')
        ];
        $expected = [
            'key' => 'value',
            Config::API_FLOW_OPTION => __(
                'Two step Magento Checkout with Resurs payment methods (deprecated)'
            ),
        ];
        $flowMock = $this->createMock(Flow::class);
        self::assertEquals($expected, $this->addFlowOption->afterToArray($flowMock, $inData));
    }
}
