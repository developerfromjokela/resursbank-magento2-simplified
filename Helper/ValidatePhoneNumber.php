<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use function preg_match;

class ValidatePhoneNumber extends AbstractHelper
{
    /**
     * Validates a Norwegian phone number.
     *
     * @param string $val
     * @return bool
     */
    public function norway(
        string $val
    ): bool {
        return (bool) preg_match(
            '/^(\+47|0047|)?[ |-]?[2-9]([ |-]?[0-9]){7,7}$/',
            $val
        );
    }
}
