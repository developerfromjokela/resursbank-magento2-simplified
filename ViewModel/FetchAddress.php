<?php
/**
 * Copyright 2016 Resurs Bank AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\ViewModel;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Resursbank\Simplified\Helper\Api;

/**
 * View model for Resurs Bank's widget to fetch a customer's address based on a
 * given SSN or organisation number.
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
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Returns the term used to refer to the customer as a private citizen
     * within Resurs Bank's API.
     *
     * @return string
     */
    public function getPrivateCustomerType(): string
    {
        return Api::CUSTOMER_TYPE_PRIVATE;
    }

    /**
     * Returns the term used to refer to the customer as an
     * organization/company within Resurs Bank's API.
     *
     * @return string
     */
    public function getCompanyCustomerType(): string
    {
        return Api::CUSTOMER_TYPE_COMPANY;
    }
}
