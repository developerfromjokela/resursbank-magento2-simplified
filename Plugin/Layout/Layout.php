<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

namespace Resursbank\Simplified\Plugin\Layout;

use Exception;
use Magento\Checkout\Block\Checkout\LayoutProcessor\Interceptor;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\ValidatorException;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Api\PaymentMethodRepositoryInterface;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\Log;

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
     * @var Credentials
     */
    private $credentials;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepo;

    /**
     * @var Log
     */
    private $log;

    /**
     * @param Credentials $credentials
     * @param SearchCriteriaBuilder $searchBuilder
     * @param PaymentMethodRepositoryInterface $paymentMethodRepo
     */
    public function __construct(
        Credentials $credentials,
        SearchCriteriaBuilder $searchBuilder,
        PaymentMethodRepositoryInterface $paymentMethodRepo
    ) {
        $this->searchBuilder = $searchBuilder;
        $this->credentials = $credentials;
        $this->paymentMethodRepo = $paymentMethodRepo;
    }

    /**
     * @param Interceptor $subject
     * @param array $result
     * @return array
     * @throws Exception
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
                $methods = array_filter(
                    $this->getPaymentMethods(),
                    static function ($method) {
                        return isset($method['active']) &&
                            $method['active'] === '1';
                    }
                );

                foreach ($methods as $method) {
                    $result['components']['checkout']['children']['steps']
                    ['children']['billing-step']['children']['payment']['children']
                    ['renders']['children']['resursbank-simplified']['methods']
                    [$method['code']]['isBillingAddressRequired'] = true;
                }
            }

//            if (isset($result['components']['checkout']['children']['steps']
//                ['children']['shipping-step']['children']['shippingAddress']
//                ['children']['shipping-address-fieldset']['children']
//                ['resursbank-simplified-telephone-validation'])
//            ) {
//                if (!$this->configAdvanced->isValidatePhoneNumberEnabled()) {
//                    unset(
//                        $result['components']['checkout']['children']['steps']
//                        ['children']['shipping-step']['children']['shippingAddress']
//                        ['children']['shipping-address-fieldset']['children']
//                        ['resursbank-simplified-telephone-validation']
//                    );
//                }
//            }
        } catch (Exception $e) {
            $this->log->error($e);
            throw $e;
        }

        return [$result];
    }

    /**
     * Get the payment methods for the current user.
     *
     * @return array
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
}
