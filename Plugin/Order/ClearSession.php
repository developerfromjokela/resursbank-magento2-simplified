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
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Session as SessionHelper;

/**
 * Clear session data after order placement.
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
     * @param Log $log
     * @param SessionHelper $sessionHelper
     */
    public function __construct(
        Log $log,
        SessionHelper $sessionHelper
    ) {
        $this->log = $log;
        $this->sessionHelper = $sessionHelper;
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
            $this->sessionHelper->unsetAll();
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }
}
