<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Order;

use Exception;
use Magento\Checkout\Controller\Onepage\Success;
use Magento\Framework\Controller\ResultInterface;
use Resursbank\Simplified\Helper\Config;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Session as SessionHelper;

/**
 * Clear this module's own session data after a successful order placement. The
 * checkout session will not be touched.
 */
class ClearSession
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @var SessionHelper
     */
    private $sessionHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Log $log
     * @param SessionHelper $sessionHelper
     * @param Config $config
     */
    public function __construct(
        Log $log,
        SessionHelper $sessionHelper,
        Config $config
    ) {
        $this->log = $log;
        $this->sessionHelper = $sessionHelper;
        $this->config = $config;
    }

    /**
     * @param Success $subject
     * @param ResultInterface $result
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterExecute(
        Success $subject,
        ResultInterface $result
    ): ResultInterface {
        try {
            if ($this->config->isActive()) {
                $this->sessionHelper->unsetAll();
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }
}
