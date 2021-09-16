<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Resursbank\Core\Helper\AbstractConfig;
use Resursbank\Core\Helper\Config as CoreConfig;

class Config extends AbstractConfig
{
    /**
     * @var CoreConfig
     */
    private $coreConfig;

    /**
     * API flow option appended by this module.
     */
    public const API_FLOW_OPTION = 'simplified';

    /**
     * @param ScopeConfigInterface $reader
     * @param WriterInterface $writer
     * @param CoreConfig $coreConfig
     * @param Context $context
     */
    public function __construct(
        ScopeConfigInterface $reader,
        WriterInterface $writer,
        CoreConfig $coreConfig,
        Context $context
    ) {
        $this->coreConfig = $coreConfig;

        parent::__construct($reader, $writer, $context);
    }

    /**
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function isActive(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): bool {
        return $this->coreConfig->getFlow($scopeCode, $scopeType) ===
            self::API_FLOW_OPTION;
    }

    /**
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function isWaitingForFraudControl(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): bool {
        return $this->isEnabled(
            CoreConfig::ADVANCED_GROUP,
            'wait_for_fraud_control',
            $scopeCode,
            $scopeType
        );
    }

    /**
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function isAnnulIfFrozen(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): bool {
        return $this->isEnabled(
            CoreConfig::ADVANCED_GROUP,
            'annul_if_frozen',
            $scopeCode,
            $scopeType
        );
    }

    /**
     * @param null|string $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function isFinalizeIfBooked(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): bool {
        return $this->isEnabled(
            CoreConfig::ADVANCED_GROUP,
            'finalize_if_booked',
            $scopeCode,
            $scopeType
        );
    }

    /**
     * Manually defined maximum transaction amount for Swish.
     *
     * @param null|string $scopeCode
     * @param string $scopeType
     * @return float
     */
    public function getSwishMaxOrderTotal(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): float {
        return (float) $this->get(
            CoreConfig::ADVANCED_GROUP,
            'swish_max_order_total',
            $scopeCode,
            $scopeType
        );
    }
}
