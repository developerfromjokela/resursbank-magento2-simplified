<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

namespace Resursbank\Simplified\Plugin\Layout;

use Exception;
use Magento\Checkout\Block\Checkout\LayoutProcessor\Interceptor;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;

/**
 * Injects 'isBillingAddressRequired' property for all our payment methods in
 * the compiled layout XML. This is to ensure the billing address form section
 * is displayed for all our payment methods without us needing to specify the
 * requirement in the layout XML for each payment method (since the methods are
 * dynamically named for each account this is not a possibility for us).
 */
class Layout
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @var Log
     */
    private $helper;

    /**
     * @param Log $log
     * @param PaymentMethods $helper
     */
    public function __construct(
        Log $log,
        PaymentMethods $helper
    ) {
        $this->log = $log;
        $this->helper = $helper;
    }

    /**
     * @param Interceptor $subject
     * @param array $result
     * @return array
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function beforeProcess(
        Interceptor $subject,
        array $result
    ): array {
        try {
            if (isset($result['components']['checkout']['children']['steps']
                ['children']['billing-step']['children']
                ['payment']['children'])
            ) {
                foreach ($this->helper->getMethodsByCredentials() as $method) {
                    $result['components']['checkout']['children']['steps']
                    ['children']['billing-step']['children']['payment']
                    ['children']['renders']['children']['resursbank-simplified']
                    ['methods'][$method['code']]
                    ['isBillingAddressRequired'] = true;
                }
            }
        } catch (Exception $e) {
            $this->log->error($e);
            throw $e;
        }

        return [$result];
    }
}
