<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\ViewModel;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Resursbank\Simplified\Helper\Address as FetchAddressHelper;

/**
 * View model for Resurs Bank's widget to fetch a customer's address based on
 * provided SSN or organisation number.
 */
class FetchAddress implements ArgumentInterface
{
    /**
     * @var FormKey
     */
    private FormKey $formKey;

    /**
     * @param FormKey $formKey
     */
    public function __construct(
        FormKey $formKey
    ) {
        $this->formKey = $formKey;
    }

    /**
     * Generate form key.
     *
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Get private citizen customer type identification string.
     *
     * @return string
     */
    public function getPrivateCustomerType(): string
    {
        return FetchAddressHelper::CUSTOMER_TYPE_PRIVATE;
    }

    /**
     * Get company customer type identification string.
     *
     * @return string
     */
    public function getCompanyCustomerType(): string
    {
        return FetchAddressHelper::CUSTOMER_TYPE_COMPANY;
    }
}
