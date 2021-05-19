<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Helper;

use Resursbank\Core\Helper\Url as Subject;

/**
 * Appends '/#payment' to the URL we redirect clients to after the cart has been
 * rebuilt (failure page -> rebuild cart -> redirect URL). This ensures clients
 * land on step two of the checkout process.
 */
class Url
{
    /**
     * @param Subject $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterGetCheckoutRebuildRedirectUrl(
        Subject $subject,
        string $result
    ): string {
        return $result . '/#payment';
    }
}
