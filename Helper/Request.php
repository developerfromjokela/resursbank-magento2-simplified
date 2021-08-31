<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use function is_bool;
use function is_string;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Exception\MissingRequestParameterException;
use Resursbank\Core\Helper\Config as CoreConfig;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Request extends AbstractHelper
{
    /**
     * @var ResultFactory
     */
    private ResultFactory $resultFactory;

    /**
     * @var Log
     */
    private Log $log;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var ValidateGovId
     */
    private ValidateGovId $validateGovId;

    /**
     * @var CoreConfig
     */
    private CoreConfig $config;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ValidatePhoneNumber
     */
    private ValidatePhoneNumber $validatePhoneNumber;

    /**
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param Log $log
     * @param RequestInterface $request
     * @param ValidateGovId $validateGovId
     * @param ValidatePhoneNumber $validatePhoneNumber
     * @param CoreConfig $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        Log $log,
        RequestInterface $request,
        ValidateGovId $validateGovId,
        ValidatePhoneNumber $validatePhoneNumber,
        CoreConfig $config,
        StoreManagerInterface $storeManager
    ) {
        $this->resultFactory = $resultFactory;
        $this->log = $log;
        $this->request = $request;
        $this->validateGovId = $validateGovId;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->validatePhoneNumber = $validatePhoneNumber;

        parent::__construct($context);
    }

    /**
     * Retrieve JSON response object.
     *
     * @param array<string, mixed> $data
     * @return Json
     * @throws Exception
     */
    public function getResponse(
        array $data
    ): Json {
        try {
            /** @var Json $result */
            $result = $this->resultFactory->create(
                ResultFactory::TYPE_JSON
            );

            $result->setData($data);
        } catch (Exception $e) {
            $this->log->exception($e);
            throw $e;
        }

        return $result;
    }

    /**
     * Retrieve the is_company request parameter as a bool.
     *
     * @return bool
     * @throws MissingRequestParameterException
     */
    public function isCompany(): bool
    {
        $result = $this->request->getParam('is_company');

        if ($result === 'true') {
            $result = true;
        } elseif ($result === 'false') {
            $result = false;
        }

        if (!is_bool($result)) {
            throw new MissingRequestParameterException(
                __('Parameter [is_company] was not set or isn\'t a bool.')
            );
        }

        return $result;
    }

    /**
     * Validates and returns identifier value utilised to fetch address.
     *
     * @param bool $isCompany
     * @return string
     * @throws InvalidDataException
     * @throws MissingRequestParameterException
     * @throws NoSuchEntityException
     */
    public function getIdentifier(
        bool $isCompany
    ): string {
        $result = $this->request->getParam('identifier');

        if (!is_string($result)) {
            throw new MissingRequestParameterException(
                __('Parameter [identifier] was not set or isn\'t a string.')
            );
        }

        $country = $this->config->getDefaultCountry(
            $this->storeManager->getStore()->getCode()
        );

        if ($country === 'SE' &&
            !$this->validateGovId->sweden($result, $isCompany)
        ) {
            throw new InvalidDataException(
                __('Invalid Swedish government ID.')
            );
        } elseif ($country === 'NO' &&
            !$this->validatePhoneNumber->norway($result)
        ) {
            throw new InvalidDataException(
                __('Invalid phone number.')
            );
        }

        return $result;
    }

    /**
     * Validates and returns the gov_id request parameter.
     *
     * @param bool $isCompany
     * @return string
     * @throws InvalidDataException
     * @throws MissingRequestParameterException
     * @throws NoSuchEntityException
     */
    public function getGovId(
        bool $isCompany
    ): string {
        $result = $this->request->getParam('gov_id');

        if (!is_string($result)) {
            throw new MissingRequestParameterException(
                __('Parameter [gov_id] was not set or isn\'t a string.')
            );
        }

        $country = $this->config->getDefaultCountry(
            $this->storeManager->getStore()->getCode()
        );

        if (!$this->validateGovId->validate($result, $isCompany, $country)) {
            throw new InvalidDataException(
                __('Invalid government ID.')
            );
        }

        return $result;
    }

    /**
     * Returns the "contact_gov_id" request parameter as a string.
     *
     * NOTE: this value will only be present if the client is a company.
     *
     * @return string
     * @throws InvalidDataException
     * @throws MissingRequestParameterException
     * @throws NoSuchEntityException
     */
    public function getContactGovId(): string
    {
        $result = $this->request->getParam('contact_gov_id');

        if (!is_string($result)) {
            throw new MissingRequestParameterException(__(
                'Parameter [contact_gov_id] was not set (or isn\'t a string) ' .
                'and is required when the customer is a company.'
            ));
        }

        $country = $this->config->getDefaultCountry(
            $this->storeManager->getStore()->getCode()
        );

        if (!$this->validateGovId->validate($result, false, $country)) {
            throw new InvalidDataException(__(
                'Invalid government ID.'
            ));
        }

        return $result;
    }

    /**
     * Converts and returns the "method_code" request parameter as a string, if
     * possible.
     *
     * @return string|null - Null if the parameter wasn't set.
     * @throws InvalidDataException
     */
    public function getMethodCode(): ?string
    {
        $result = $this->request->getParam('method_code');

        if ($result !== null && !is_string($result)) {
            throw new InvalidDataException(__('Invalid method code.'));
        }

        return $result !== null ? (string) $result : null;
    }
}
