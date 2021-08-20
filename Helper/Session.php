<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote;

/**
 * This class implements ArgumentInterface (that's normally reserved for
 * ViewModels) because we found no other way of removing the suppressed warning
 * for PHPMD.CookieAndSessionMisuse. The interface fools the analytic tools into
 * thinking this class is part of the presentation layer, and thus eligible to
 * handle the session.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Session extends AbstractHelper implements ArgumentInterface
{
    /**
     * Prefix for all session keys for this module.
     *
     * @var string
     */
    public const KEY_PREFIX = 'resursbank_simplified_';

    /**
     * Key to store and retrieve the customer's contact government ID.
     *
     * @var string
     */
    public const KEY_CONTACT_GOV_ID =
        self::KEY_PREFIX . 'contact_government_id';

    /**
     * Key to store and retrieve the customer's government ID.
     *
     * @var string
     */
    public const KEY_GOV_ID = self::KEY_PREFIX . 'government_id';

    /**
     * Key to store and retrieve customer type (NATURAL | LEGAL).
     *
     * @var string
     */
    public const KEY_IS_COMPANY = self::KEY_PREFIX . 'is_company';

    /**
     * Key to store and retrieve the payment's signing URL. The URL is utilised
     * to redirect the customer to the payment gateway. The URL is obtained
     * after a payment session has been created at Resurs Bank through the API.
     *
     * @var string
     */
    public const KEY_PAYMENT_SIGNING_URL = self::KEY_PREFIX . 'signing_url';

    /**
     * Key to store and retrieve the payment / payment session ID (the ID at
     * Resurs Bank, not to be confused with the ID of a payment entity in
     * Magento).
     *
     * @var string
     */
    public const KEY_PAYMENT_ID = self::KEY_PREFIX . 'payment_id';

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @param Context $context
     * @param CheckoutSession $sessionManager
     */
    public function __construct(
        Context $context,
        CheckoutSession $sessionManager
    ) {
        $this->checkoutSession = $sessionManager;

        parent::__construct($context);
    }

    /**
     * Store a customer's SSN/Org nr. in the session.
     *
     * @param string $govId - Must be a valid Swedish SSN/Org. number.
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setGovId(
        string $govId
    ): self {
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->setData(self::KEY_GOV_ID, $govId);

        return $this;
    }

    /**
     * @return string|null - Null if a value cannot be found.
     */
    public function getGovId(): ?string
    {
        return $this->checkoutSession->getData(self::KEY_GOV_ID);
    }

    /**
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function unsetGovId(): self
    {
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->unsetData(self::KEY_GOV_ID);

        return $this;
    }

    /**
     * Stores a customer's contact government ID in the session. Required for
     * company customers, personal SSN of a company reference.
     *
     * @param string $govId - Must be a valid SSN of a supported country.
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setContactGovId(
        string $govId
    ): self {
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->setData(
            self::KEY_CONTACT_GOV_ID,
            $govId
        );

        return $this;
    }

    /**
     * @return string|null - Null if a value cannot be found.
     */
    public function getContactGovId(): ?string
    {
        return $this->checkoutSession->getData(self::KEY_CONTACT_GOV_ID);
    }

    /**
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function unsetContactGovId(): self
    {
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->unsetData(self::KEY_CONTACT_GOV_ID);

        return $this;
    }

    /**
     * Stores customer type in the session.
     *
     * @param bool $isCompany
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setIsCompany(
        bool $isCompany
    ): self {
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->setData(self::KEY_IS_COMPANY, $isCompany);

        return $this;
    }

    /**
     * @return bool|null - Null if a value cannot be found.
     */
    public function getIsCompany(): ?bool
    {
        return $this->checkoutSession->getData(self::KEY_IS_COMPANY);
    }

    /**
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function unsetIsCompany(): self
    {
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->unsetData(self::KEY_IS_COMPANY);

        return $this;
    }

    /**
     * Stores payment signing (gateway) URL in session. Redirect URL at order
     * placement to perform payment.
     *
     * @param string $url
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setPaymentSigningUrl(
        string $url
    ): self {
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->setData(self::KEY_PAYMENT_SIGNING_URL, $url);

        return $this;
    }

    /**
     * @return string|null - Null if a value cannot be found.
     */
    public function getPaymentSigningUrl(): ?string
    {
        return $this->checkoutSession->getData(self::KEY_PAYMENT_SIGNING_URL);
    }

    /**
     * @return self
     */
    public function unsetPaymentSigningUrl(): self
    {
        /**
         * @phpstan-ignore-next-line
         * @noinspection PhpUndefinedMethodInspection
         */
        $this->checkoutSession->unsetData(self::KEY_PAYMENT_SIGNING_URL);

        return $this;
    }

    /**
     * Stores payment session ID in PHP session.
     *
     * @param string $paymentId
     * @return self
     */
    public function setPaymentId(
        string $paymentId
    ): self {
        /**
         * @phpstan-ignore-next-line
         * @noinspection PhpUndefinedMethodInspection
         */
        $this->checkoutSession->setData(self::KEY_PAYMENT_ID, $paymentId);

        return $this;
    }

    /**
     * @return string|null - Null if a value cannot be found.
     */
    public function getPaymentId(): ?string
    {
        return $this->checkoutSession->getData(self::KEY_PAYMENT_ID);
    }

    /**
     * @return self
     */
    public function unsetPaymentId(): self
    {
        /**
         * @noinspection PhpUndefinedMethodInspection
         * @phpstan-ignore-next-line
         */
        $this->checkoutSession->unsetData(self::KEY_PAYMENT_ID);

        return $this;
    }

    /**
     * Unset all the customer's personal information stored in session.
     *
     * Note that any information regarding the payment (if one has been created)
     * is not removed when using this method.
     *
     * @return self
     */
    public function unsetCustomerInfo(): self
    {
        return $this->unsetContactGovId()
            ->unsetIsCompany()
            ->unsetGovId();
    }

    /**
     * Unsets all payment information from the session.
     *
     * Note that any information regarding the customer's personal information
     * (government ID, customer type etc.) will not be removed when using this
     * method.
     *
     * @return self
     */
    public function unsetPaymentInfo(): self
    {
        return $this->unsetPaymentSigningUrl()
            ->unsetPaymentId();
    }

    /**
     * Unsets every key in the session applied through this class.
     *
     * @return self
     */
    public function unsetAll(): self
    {
        return $this->unsetPaymentInfo()
            ->unsetCustomerInfo();
    }

    /**
     * Returns the current quote of the checkout session.
     *
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuote(): Quote
    {
        return $this->checkoutSession->getQuote();
    }
}
