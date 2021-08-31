<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Setup\Patch\Data;

use Resursbank\Core\Setup\Patch\Data\RemapConfigPaths as Core;

/**
 * @inheridoc
 */
class RemapConfigPaths extends Core
{
    /**
     * NOTE: The paths appear the same but the base section has changed. We
     * must define these values to convert the base section from
     * 'resursbank_checkout' to 'resursbank'.
     *
     * @inheridoc
     * @return array<string, string>
     */
    protected function getKeys(): array
    {
        return [
            'advanced/wait_for_fraud_control' => 'advanced/wait_for_fraud_control',
            'advanced/annul_if_frozen' => 'advanced/annul_if_frozen',
            'advanced/finalize_if_booked' => 'advanced/finalize_if_booked'
        ];
    }
}
