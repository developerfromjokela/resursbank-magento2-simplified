<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Config;

use Resursbank\Core\Model\Config\Source\Flow\Interceptor as Subject;

class AddConfigOption
{
    /**
     * Adding the simplified flow to the availbale Checkout Types at the
     * core setting called "Checkout Type".
     *
     * @param Subject $subject
     * @param array $result
     * @return array
     */
    public function afterToArray(Subject $subject, array $result): array
    {
        $result['simplified'] = 'Resursbank Simplified Flow';

        return $result;
    }
}
