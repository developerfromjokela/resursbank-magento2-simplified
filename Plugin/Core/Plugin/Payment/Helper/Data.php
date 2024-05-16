<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Core\Plugin\Payment\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Resursbank\Core\Plugin\Payment\Helper\Data as Subject;
use Resursbank\Core\Helper\Scope;
use Resursbank\Simplified\Helper\Config;

/**
 * Interceptor for Core payment method filtering.
 */
class Data
{
    /**
     * @param Config $config
     * @param Scope $scope
     */
    public function __construct(
        private readonly Config $config,
        private readonly Scope $scope
    ) {
    }

    /**
     * Return true if this flow is active for the current scope.
     *
     * @param Subject $subject
     * @param callable $proceed
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSwishMaxOrderLimitApplicable(
        Subject $subject,
        callable $proceed
    ): bool {
        if ($this->config->isActive(scopeCode: $this->scope->getId())) {
            return true;
        }

        return $proceed();
    }
}
