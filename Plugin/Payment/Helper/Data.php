<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Payment\Helper;

use Exception;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\Config as CoreConfig;
use Resursbank\Core\Plugin\Payment\Helper\Data as Subject;
use Resursbank\Simplified\Helper\Config;
use Resursbank\Simplified\Helper\Log;

class Data
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var CoreConfig
     */
    private CoreConfig $coreConfig;

    /**
     * @param Log $log
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param CoreConfig $coreConfig
     */
    public function __construct(
        Log $log,
        Config $config,
        StoreManagerInterface $storeManager,
        CoreConfig $coreConfig,
    ) {
        $this->log = $log;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->coreConfig = $coreConfig;
    }

    /**
     * @param Subject $subject
     * @param PaymentMethodInterface|null $result
     * @return PaymentMethodInterface|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterGetResursModel(
        Subject $subject,
        ?PaymentMethodInterface $result
    ): ?PaymentMethodInterface {
        try {
            $store = $this->storeManager->getStore()->getCode();

            if ($result !== null &&
                $result->getSpecificType() === 'SWISH' &&
                $this->isEnabled(storeCode: $store)
            ) {
                $maxOrderTotal = $this->config->getSwishMaxOrderTotal($store);

                if ($maxOrderTotal > 0) {
                    $result->setMaxOrderTotal($maxOrderTotal);
                }
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * @param string $storeCode
     * @return bool
     */
    private function isEnabled(string $storeCode): bool
    {
        return $this->config->isActive(scopeCode: $storeCode) ||
            $this->coreConfig->isMapiActive(scopeCode: $storeCode);
    }
}
