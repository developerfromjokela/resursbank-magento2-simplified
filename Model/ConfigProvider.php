<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Model;

use Exception;
use JsonException;
use Magento\Checkout\Model\ConfigProviderInterface;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Core\Model\PaymentMethod;
use Resursbank\Core\Helper\PaymentMethods;

/**
 * Gather all of our payment methods and put them in their own section of the
 * "checkoutConfig" object on the checkout page.
 */
class ConfigProvider implements ConfigProviderInterface
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
            foreach ($this->helper->getMethodsByCredentials() as $method) {
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
     * @throws JsonException
     */
    private function mapPaymentMethod(
        PaymentMethodInterface $method
    ): array {
        $rawValue = $method->getRaw('');
        $decoded = $rawValue !== '' && $rawValue !== null ?
            json_decode(
                $rawValue,
                true,
                512,
                JSON_THROW_ON_ERROR
            ) :
            [];

        return [
            'code' => $method->getCode(),
            'title' => $method->getTitle(),
            'maxOrderTotal' => $method->getMaxOrderTotal(),
            'type' => $decoded['type'] ?? '',
            'specificType' => $decoded['specificType'] ?? ''
        ];
    }
}
