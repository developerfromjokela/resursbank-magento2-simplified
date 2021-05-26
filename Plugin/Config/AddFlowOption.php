<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Config;

use Magento\Framework\Phrase;
use Resursbank\Core\Model\Config\Source\Flow as Subject;
use Resursbank\Simplified\Helper\Config;

class AddFlowOption
{
    /**
     * Add Simplified Flow to the list of available API flows in Core module.
     *
     * @param Subject $subject
     * @param array<string, Phrase> $result
     * @return array<string, Phrase>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterToArray(
        Subject $subject,
        array $result
    ): array {
        $result[Config::API_FLOW_OPTION] = __(
            'Two step Magento Checkout with Resurs payment methods'
        );

        return $result;
    }
}
