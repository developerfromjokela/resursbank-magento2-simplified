<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Helper;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Helper\Url as Subject;
use Resursbank\Simplified\Helper\Config;

/**
 * Appends '/#payment' to the URL we redirect clients to after the cart has been
 * rebuilt (failure page -> rebuild cart -> redirect URL). This ensures clients
 * land on step two of the checkout process.
 */
class Url
{
    /**
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly Config $config
    ) {
    }

    /**
     * Intercept calls to the getCheckoutRebuildRedirectUrl method.
     *
     * @param Subject $subject
     * @param string $result
     * @return string
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCheckoutRebuildRedirectUrl(
        Subject $subject,
        string $result
    ): string {
        if (!$this->config->isActive(
            scopeCode: $this->storeManager->getStore()->getCode()
        )) {
            return $result;
        }

        return $result . '/#payment';
    }
}
