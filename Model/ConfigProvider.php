<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Model;

use Exception;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Simplified\Helper\Log;

/**
 * Gather all of our payment methods and put them in their own section of the
 * "checkoutConfig" object on the checkout page.
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Log
     */
    private Log $log;

    /**
     * @var PaymentMethods
     */
    private PaymentMethods $helper;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

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
     * Builds this module's section in the config provider.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $result = [
            'payment' => [
                'resursbank_simplified' => [
                    'methods' => []
                ]
            ]
        ];

        try {
            $methods = $this->helper->getMethodsByCredentials(
                $this->storeManager->getStore()->getCode(),
                ScopeInterface::SCOPE_STORES
            );

            foreach ($methods as $method) {
                $result['payment']['resursbank_simplified']['methods'][] =
                    $this->mapPaymentMethod($method);
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * Maps a payment method for the config provider. Note that not all data
     * from the payment method will be mapped in this process.
     *
     * @param PaymentMethodInterface $method
     * @return array<string, mixed>
     */
    private function mapPaymentMethod(
        PaymentMethodInterface $method
    ): array {
        $data = $this->helper->getRaw($method);

        return [
            'code' => $method->getCode(),
            'title' => $method->getTitle(),
            'maxOrderTotal' => $method->maxOrderTotal(),
            'sortOrder' => $method->getSortOrder(0),
            'type' => $data['type'] ?? '',
            'specificType' => $data['specificType'] ?? '',
            'customerType' => $this->helper->getCustomerTypes($method)
        ];
    }
}
