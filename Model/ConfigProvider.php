<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Core\Helper\Config;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Module\PaymentMethod\Repository as PaymentMethodRepository;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Core\ViewModel\Session\Checkout as CheckoutSession;
use Throwable;

/**
 * Gather all of our payment methods and put them in their own section of the
 * "checkoutConfig" object on the checkout page.
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @param Log $log
     * @param Config $config
     * @param PaymentMethods $helper
     * @param StoreManagerInterface $storeManager
     * @param CheckoutSession $session
     */
    public function __construct(
        private readonly Log $log,
        private readonly Config $config,
        private readonly PaymentMethods $helper,
        private readonly StoreManagerInterface $storeManager,
        private readonly CheckoutSession $session
    ) {
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
                scopeCode: $this->storeManager->getStore()->getCode(),
                scopeType: ScopeInterface::SCOPE_STORES
            );

            foreach ($methods as $method) {
                $result['payment']['resursbank_simplified']['methods'][] =
                    $this->mapPaymentMethod(method: $method);
            }
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return $result;
    }

    /**
     * Maps selective data from payment method to the config provider.
     *
     * @param PaymentMethodInterface $method
     * @return array<string, mixed>
     * @throws NoSuchEntityException
     */
    private function mapPaymentMethod(
        PaymentMethodInterface $method
    ): array {
        $data = $this->helper->getRaw(method: $method);

        return [
            'code' => $method->getCode(),
            'title' => $method->getTitle(),
            'maxOrderTotal' => $method->getMaxOrderTotal(),
            'sortOrder' => $method->getSortOrder(default: 0),
            'type' => $data['type'] ?? '',
            'specificType' => $data['specificType'] ?? '',
            'customerType' => $this->helper->getCustomerTypes(method: $method),
            'usp' => $this->config->isMapiActive(
                scopeCode: $this->storeManager->getStore()->getCode()
            ) ? $this->getUspMessage(methodCode: $method->getCode()) : ''
        ];
    }

    /**
     * Fetches the USP message for specified method code.
     *
     * @param string $methodCode
     * @return string
     */
    private function getUspMessage(string $methodCode): string
    {
        try {
            return PaymentMethodRepository::getUniqueSellingPoint(
                paymentMethod: $this->getMapiPaymentMethod(methodCode: $methodCode),
                amount: (float)$this->session->getQuote()->getGrandTotal()
            )->message;
        } catch (Throwable) {
            return '';
        }
    }

    /**
     * Fetches the MAPI payment method that corresponds to the supplied Magento method code.
     *
     * @param string $methodCode
     * @return PaymentMethod|null
     * @throws IllegalValueException
     */
    private function getMapiPaymentMethod(
        string $methodCode
    ): ?PaymentMethod {
        if (!str_starts_with(haystack: $methodCode, needle: 'resursbank_')) {
            throw new IllegalValueException(
                message: 'Method code cannot be parsed.'
            );
        }

        $paymentMethodId = substr(string: $methodCode, offset: 11);

        try {
            return PaymentMethodRepository::getById(
                storeId: $this->config->getStore(
                    scopeCode: $this->storeManager->getStore()->getCode()
                ),
                paymentMethodId: $paymentMethodId
            );
        } catch (Throwable) {
            return null;
        }
    }
}
