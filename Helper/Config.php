<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Magento\Store\Model\ScopeInterface;
use Resursbank\Core\Helper\AbstractConfig;

class Config extends AbstractConfig
{
    /**
     * @var string
     */
    public const GROUP = 'simplified';

    /**
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function isActive(
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): bool {
        return true;
    }

    /**
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function isDebugEnabled(
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): bool {
        return $this->isEnabled(
            self::GROUP,
            'debug',
            $scopeCode,
            $scopeType
        );
    }

    /**
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function isWaitingForFraudControl(
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): bool {
        return $this->isEnabled(
            self::GROUP,
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
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): bool {
        return $this->isEnabled(
            self::GROUP,
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
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): bool {
        return $this->isEnabled(
            self::GROUP,
            'finalize_if_booked',
            $scopeCode,
            $scopeType
        );
    }
}
