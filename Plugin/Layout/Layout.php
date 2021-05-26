<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Layout;

use Exception;
use Magento\Checkout\Block\Checkout\LayoutProcessor\Interceptor;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Simplified\Helper\Log;
use function is_string;

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
     * @var PaymentMethods
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Log $log
     * @param PaymentMethods $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Log $log,
        PaymentMethods $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->log = $log;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Interceptor $subject
     * @param array<mixed> $result
     * @return array<int, array>
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
                $methods = $this->helper->getMethodsByCredentials(
                    $this->storeManager->getStore()->getCode(),
                    ScopeInterface::SCOPE_STORES
                );

                foreach ($methods as $method) {
                    $code = $method->getCode();

                    if (!is_string($code)) {
                        new InvalidDataException(__(
                            'Payment method does not have a code.'
                        ));
                    }

                    $result['components']['checkout']['children']['steps']
                    ['children']['billing-step']['children']['payment']
                    ['children']['renders']['children']['resursbank-simplified']
                    ['methods'][$code]
                    ['isBillingAddressRequired'] = true;
                }
            }
        } catch (Exception $e) {
            $this->log->exception($e);
            throw $e;
        }

        return [$result];
    }
}
