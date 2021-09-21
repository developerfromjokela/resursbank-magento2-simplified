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
     * @param Log $log
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Log $log,
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->log = $log;
        $this->config = $config;
        $this->storeManager = $storeManager;
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
                $this->config->isActive($store)
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
}
