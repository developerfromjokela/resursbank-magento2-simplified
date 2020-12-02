<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class ValidateCard extends AbstractHelper
{
    /**
     * Validates a card.
     *
     * @param string $num
     * @param bool $allowEmptyId
     * @return bool
     */
    public function validate(
        string $num,
        bool $allowEmptyId = false
    ): bool {
        return $allowEmptyId && $num === '' ?
            true :
            (bool) preg_match(
                '/^([1-9][0-9]{3}[ ]{0,1}[0-9]{4}' .
                '[ ]{0,1}[0-9]{4}[ ]{0,1}[0-9]{4})$/',
                $num
            );
    }
}
