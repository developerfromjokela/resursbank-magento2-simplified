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
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\InvalidDataException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Session extends AbstractHelper
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
    public const KEY_CONTACT_GOVERNMENT_ID =
        self::KEY_PREFIX . 'contact_government_id';

    /**
     * Key to store and retrieve the customer's government ID.
     *
     * @var string
     */
    public const KEY_GOVERNMENT_ID =
        self::KEY_PREFIX . 'government_id';

    /**
     * Key to store and retrieve customer type (NATURAL | LEGAL).
     *
     * @var string
     */
    public const KEY_IS_COMPANY = self::KEY_PREFIX . 'is_company';

    /**
     * Key to store and retrieve the selected Resurs Bank card amount.
     *
     * @var string
     */
    public const KEY_CARD_AMOUNT = self::KEY_PREFIX . 'card_amount';

    /**
     * Key to store and retrieve the Resurs Bank card number (not to be confused
     * with CC number).
     *
     * @var string
     */
    public const KEY_CARD_NUMBER = self::KEY_PREFIX . 'card_number';

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
    private $checkoutSession;

    /**
     * @var ValidateGovernmentId
     */
    private $validateGovId;

    /**
     * @var ValidateCard
     */
    private $validateCard;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param CheckoutSession $sessionManager
     * @param ValidateGovernmentId $validateGovId
     * @param ValidateCard $validateCard
     * @param UrlInterface $url
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        CheckoutSession $sessionManager,
        ValidateGovernmentId $validateGovId,
        ValidateCard $validateCard,
        UrlInterface $url,
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->checkoutSession = $sessionManager;
        $this->validateGovId = $validateGovId;
        $this->validateCard = $validateCard;
        $this->url = $url;
        $this->config = $config;
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }

    /**
     * Store a customer's SSN/Org nr. in the session.
     *
     * @param string $govId - Must be a valid swedish SSN/Org. number.
     * @param bool $isCompany
     * @return self
     * @throws InvalidDataException
     * @throws NoSuchEntityException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setGovernmentId(
        string $govId,
        bool $isCompany
    ): self {
        $iso = $this->config->getCountry(
            $this->storeManager->getStore()->getCode()
        );

        if (!$this->validateGovId->validate($govId, $isCompany, $iso, true)) {
            throw new InvalidDataException(__('Invalid government ID.'));
        }

        /** @phpstan-ignore-next-line */
        $this->checkoutSession->setData(self::KEY_GOVERNMENT_ID, $govId);

        return $this;
    }

    /**
     * @return string|null - Null if a value cannot be found.
     */
    public function getGovernmentId(): ?string
    {
        return $this->checkoutSession->getData(self::KEY_GOVERNMENT_ID);
    }

    /**
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function unsetGovernmentId(): self
    {
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->unsetData(self::KEY_GOVERNMENT_ID);

        return $this;
    }

    /**
     * Stores a customer's contact government ID in the session. Required for
     * company customers, personal SSN of a company reference.
     *
     * @param string $govId - Must be a valid swedish SSN.
     * @return self
     * @throws InvalidDataException - Throws if SSN is invalid.
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setContactGovernmentId(
        string $govId
    ): self {
        if (!$this->validateGovId->swedenSsn($govId, true)) {
            throw new InvalidDataException(__('Invalid SE government ID.'));
        }

        /** @phpstan-ignore-next-line */
        $this->checkoutSession->setData(
            self::KEY_CONTACT_GOVERNMENT_ID,
            $govId
        );

        return $this;
    }

    /**
     * @return string|null - Null if a value cannot be found.
     */
    public function getContactGovernmentId(): ?string
    {
        return $this->checkoutSession->getData(self::KEY_CONTACT_GOVERNMENT_ID);
    }

    /**
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function unsetContactGovernmentId(): self
    {
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->unsetData(self::KEY_CONTACT_GOVERNMENT_ID);

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
     * Stores Resurs Bank card number in the session.
     *
     * @param string $cardNum - Must be a valid card number.
     * @return self
     * @throws InvalidDataException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setCardNumber(
        string $cardNum
    ): self {
        if (!$this->validateCard->validate($cardNum, true)) {
            throw new InvalidDataException(__('Invalid card number.'));
        }

        /** @phpstan-ignore-next-line */
        $this->checkoutSession->setData(self::KEY_CARD_NUMBER, $cardNum);

        return $this;
    }

    /**
     * @return string|null - Null if a value cannot be found.
     */
    public function getCardNumber(): ?string
    {
        return $this->checkoutSession->getData(self::KEY_CARD_NUMBER);
    }

    /**
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function unsetCardNumber(): self
    {
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->unsetData(self::KEY_CARD_NUMBER);

        return $this;
    }

    /**
     * Stores selected Resurs Bank card amount in session.
     *
     * @param float $cardAmount
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setCardAmount(
        float $cardAmount
    ): self {
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->setData(self::KEY_CARD_AMOUNT, $cardAmount);

        return $this;
    }

    /**
     * @return float|null - Null if a value cannot be found.
     */
    public function getCardAmount(): ?float
    {
        return $this->checkoutSession->getData(self::KEY_CARD_AMOUNT);
    }

    /**
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function unsetCardAmount(): self
    {
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->unsetData(self::KEY_CARD_AMOUNT);

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
     * Unsets all of the customer's personal information related to government
     * ID, customer type & card information from the session.
     *
     * Note that any information regarding the payment (if one has been created)
     * is not removed when using this method.
     *
     * @return self
     */
    public function unsetCustomerInfo(): self
    {
        return $this->unsetCardAmount()
            ->unsetCardNumber()
            ->unsetContactGovernmentId()
            ->unsetIsCompany()
            ->unsetGovernmentId();
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

    /**
     * URL client is redirect back to after successfully completing their
     * payment at the gateway.
     *
     * NOTE: We include quote id and order increment id to support intermediate
     * browser change during the signing procedure. For example, if the client
     * signs their payment at the gateway using BankID on a smart phone the
     * redirect URL may be opened in the OS default browser instead of the
     * browser utilised by the customer to perform the purchase. This means the
     * session data is lost and the order will thus fail. By including these
     * parameters we can load the data back into the session if it's missing.
     *
     * @param string $quoteId
     * @return string
     */
    public function getSuccessCallbackUrl(
        string $quoteId
    ): string {
        return $this->url->getUrl(
            'checkout/onepage/success/' .
            'quote_id/' . $quoteId
        );
    }

    /**
     * URL client is redirect back to after failing to completing their payment
     * at the gateway.
     *
     * NOTE: For information regarding the included quote and order parameters
     * please refer to the getSuccessCallbackUrl() docblock above.
     *
     * @param string $quoteId
     * @return string
     */
    public function getFailureCallbackUrl(
        string $quoteId
    ): string {
        return $this->url->getUrl(
            'checkout/onepage/failure/' .
            'quote_id/' . $quoteId
        );
    }
}
