<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Model;

use Exception;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\ValidatorException;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Api\PaymentMethodRepositoryInterface;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Model\PaymentMethod;

/**
 * Gather all of our payment methods and put them in their own section of the
 * "checkoutConfig" object on the checkout page.
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Credentials
     */
    private $credentials;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepo;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @param Credentials $credentials
     * @param Log $log
     * @param PaymentMethodRepositoryInterface $paymentMethodRepo
     * @param SearchCriteriaBuilder $searchBuilder
     */
    public function __construct(
        Credentials $credentials,
        Log $log,
        PaymentMethodRepositoryInterface $paymentMethodRepo,
        SearchCriteriaBuilder $searchBuilder
    ) {
        $this->credentials = $credentials;
        $this->log = $log;
        $this->paymentMethodRepo = $paymentMethodRepo;
        $this->searchBuilder = $searchBuilder;
    }

    /**
     * Builds this module's section in the config provider.
     *
     * @return array
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getConfig(): array
    {
        $methods = [];
        $result = [
            'payment' => [
                'resursbank_simplified' => []
            ]
        ];

        try {
            $collection = $this->getPaymentMethods();

            foreach ($collection as $method) {
                $methods[] = $this->mapPaymentMethod($method);
            }

            $result['payment']['resursbank_simplified']['methods'] = $methods;
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * Get the payment methods for the current user.
     *
     * @return PaymentMethod[]
     * @throws ValidatorException
     */
    private function getPaymentMethods(): array
    {
        $credentials = $this->credentials->resolveFromConfig();

        $searchCriteria = $this->searchBuilder->addFilter(
            PaymentMethodInterface::CODE,
            "%{$this->credentials->getMethodSuffix($credentials)}",
            'like'
        )->create();

        return $this->paymentMethodRepo->getList($searchCriteria)->getItems();
    }

    /**
     * Maps a payment method for the config provider. Note that not all data
     * from the payment method will be mapped in this process.
     *
     * @param PaymentMethod $method
     * @return array
     * @noinspection PhpComposerExtensionStubsInspection
     */
    private function mapPaymentMethod(
        PaymentMethod $method
    ): array {
        $raw = $method->getRaw('');
        $rawData = null;
        $type = '';
        $specificType = '';

        if ($raw) {
            $rawData = json_decode($raw, false);
            $type = $rawData->type;
            $specificType = $rawData->specificType;
        }

        return [
            'code' => $method->getCode(),
            'title' => $method->getTitle(),
            'type' => $type,
            'maxOrderTotal' => $method->getMaxOrderTotal(),
            'specificType' => $specificType,
        ];
    }
}
