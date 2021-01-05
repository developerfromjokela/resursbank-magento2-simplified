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
use Resursbank\Simplified\Exception\InvalidDataException;

/**
 * @SuppressWarnings(PHP.TooManyPublicMethods)
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
     * Key to store and retrieve which type the customer (company or private
     * person).
     *
     * @var string
     */
    public const KEY_IS_COMPANY = self::KEY_PREFIX . 'is_company';

    /**
     * Key to store and retrieve the customer's selected card amount.
     *
     * @var string
     */
    public const KEY_CARD_AMOUNT = self::KEY_PREFIX . 'card_amount';

    /**
     * Key to store and retrieve the customer's card number.
     *
     * @var string
     */
    public const KEY_CARD_NUMBER = self::KEY_PREFIX . 'card_number';

    /**
     * Key to store and retrieve the payment's signing URL. The URL can be used
     * to redirect the user to the payment gateway. The URL is obtained
     * after a payment in Resurs Bank's API has been created.
     *
     * @var string
     */
    public const KEY_PAYMENT_SIGNING_URL = self::KEY_PREFIX . 'signing_url';

    /**
     * Key to store and retrieve the payment's ID.
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
     * @param Context $context
     * @param CheckoutSession $sessionManager
     * @param ValidateGovernmentId $validateGovId
     * @param ValidateCard $validateCard
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        CheckoutSession $sessionManager,
        ValidateGovernmentId $validateGovId,
        ValidateCard $validateCard,
        UrlInterface $url
    ) {
        $this->checkoutSession = $sessionManager;
        $this->validateGovId = $validateGovId;
        $this->validateCard = $validateCard;
        $this->url = $url;

        parent::__construct($context);
    }

    /**
     * Stores a customer's SSN/Org nr. in the session.
     *
     * @param string $govId - Must be a valid swedish SSN/Org. number.
     * @param bool $isCompany
     * @return self
     * @throws InvalidDataException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setGovernmentId(
        string $govId,
        bool $isCompany
    ): self {
        $valid = $this->validateGovId->sweden(
            $govId,
            $isCompany,
            true
        );

        if (!$valid) {
            throw new InvalidDataException(__(
                'Invalid swedish government ID was given.'
            ));
        }

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
        $valid = $this->validateGovId->swedenSsn(
            $govId,
            true
        );

        if (!$valid) {
            throw new InvalidDataException(__(
                'Invalid swedish government ID was given.'
            ));
        }

        $this->checkoutSession->setData(self::KEY_CONTACT_GOVERNMENT_ID, $govId);

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
        $this->checkoutSession->unsetData(self::KEY_CONTACT_GOVERNMENT_ID);

        return $this;
    }

    /**
     * Stores a customer's type in the session.
     *
     * @param bool $isCompany
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setIsCompany(
        bool $isCompany
    ): self {
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
        $this->checkoutSession->unsetData(self::KEY_IS_COMPANY);

        return $this;
    }

    /**
     * Stores a customer's given Resurs Bank card number in the session.
     *
     * @param string $cardNum - Must be a valid card number.
     * @return self
     * @throws InvalidDataException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setCardNumber(
        string $cardNum
    ): self {
        $valid = $this->validateCard->validate(
            $cardNum,
            true
        );

        if (!$valid) {
            throw new InvalidDataException(__(
                'Invalid card number was given.'
            ));
        }

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
        $this->checkoutSession->unsetData(self::KEY_CARD_NUMBER);

        return $this;
    }

    /**
     * Stores a customer's selected amount that will be available on their
     * Resurs Bank card.
     *
     * @param float $cardAmount
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setCardAmount(
        float $cardAmount
    ): self {
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
        $this->checkoutSession->unsetData(self::KEY_CARD_AMOUNT);

        return $this;
    }

    /**
     * Stores the signing URL that can be used to redirect the customer to the
     * payment gateway of the chosen payment method. The URL is obtained
     * after a payment in Resurs Bank's API has been created.
     *
     * @param string $url
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setPaymentSigningUrl(
        string $url
    ): self {
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
     * @noinspection PhpUndefinedMethodInspection
     */
    public function unsetPaymentSigningUrl(): self
    {
        $this->checkoutSession->unsetData(self::KEY_PAYMENT_SIGNING_URL);

        return $this;
    }

    /**
     * Stores a payment's ID in the session.
     *
     * @param string $paymentId
     * @return self
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setPaymentId(
        string $paymentId
    ): self {
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
     * @noinspection PhpUndefinedMethodInspection
     */
    public function unsetPaymentId(): self
    {
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
     * Unsets every key in the session of this class.
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
     * Produces a URL to redirect the customer to when a purchase has been
     * successful which contains the order ID and quote ID of said purchase as
     * parameters. In the event that the customer switched to another browser
     * during the signing process, and the session is lost, we should be able
     * to retrieve the data we need with these parameters in the new browser
     * session.
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
     * Produces a URL to redirect the customer to when a purchase has failed
     * which contains the order ID and quote ID of said purchase as parameters.
     * In the event that the customer switched to another browser during the
     * signing process, and the session is lost, we should be able to retrieve
     * the data we need with these parameters in the new browser session.
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
