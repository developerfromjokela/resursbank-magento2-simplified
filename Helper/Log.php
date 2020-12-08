<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Resursbank\Core\Helper\AbstractLog;

class Log extends AbstractLog
{
    /**
     * @inheritDoc
     */
    protected $loggerName = 'Resurs Bank Simplified Log';

    /**
     * @inheritDoc
     */
    protected $file = 'resursbank_simplified';

    /**
     * @var Config
     */
    private $config;

    /**
     * @inheritDoc
     */
    public function __construct(
        DirectoryList $directories,
        Context $context,
        Config $config
    ) {
        $this->config = $config;

        parent::__construct($directories, $context);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->isDebugEnabled();
    }
}
