<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Order;

use Exception;
use Magento\Checkout\Controller\Onepage\Failure;
use Magento\Checkout\Controller\Onepage\Success;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Store\Model\StoreManagerInterface;
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
    private Log $log;

    /**
     * @var SessionHelper
     */
    private SessionHelper $sessionHelper;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param Log $log
     * @param SessionHelper $sessionHelper
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Log $log,
        SessionHelper $sessionHelper,
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->log = $log;
        $this->sessionHelper = $sessionHelper;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * Intercept calls to the execute method.
     *
     * @param Success|Failure $subject
     * @param ResultInterface|Redirect|Page $result
     * @return ResultInterface|Redirect|Page
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterExecute(
        $subject,
        $result
    ) {
        try {
            $storeCode = $this->storeManager->getStore()->getCode();

            if ($this->config->isActive($storeCode)) {
                $this->sessionHelper->unsetAll();
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }
}
