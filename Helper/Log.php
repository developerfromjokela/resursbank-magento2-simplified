<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Resursbank\Core\Helper\AbstractLog;

class Log extends AbstractLog
{
    /**
     * @inheritDoc
     */
    protected string $loggerName = 'Resurs Bank Simplified Log';

    /**
     * @inheritDoc
     */
    protected string $file = 'resursbank_simplified';
}
