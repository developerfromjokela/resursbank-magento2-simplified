<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Controller\Checkout;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;
use Magento\Framework\Controller\Result\RedirectFactory;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Session;
use Resursbank\Core\ViewModel\Session\Checkout as CheckoutSession;

/**
 * Redirect to signing page at Resurs Bank.
 */
class Redirect implements HttpGetActionInterface
{
    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;

    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;
    /**
     * @var \Magento\Store\App\Response\Redirect
     */
    private \Magento\Store\App\Response\Redirect $redirectReponse;

    /**
     * @var Log
     */
    private Log $log;

    /**
     * @param RedirectFactory $redirectFactory
     * @param Session $session
     * @param Log $log
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        Session $session,
        Log $log,
        CheckoutSession $checkoutSession,
        \Magento\Store\App\Response\Redirect $redirectReponse
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->session = $session;
        $this->checkoutSession = $checkoutSession;
        $this->redirectReponse = $redirectReponse;
        $this->log = $log;
    }

    /**
     * Redirect to signing URL. If there is none, redirect straight to success
     * page.
     *
     * @return RedirectResult
     * @throws Exception
     */
    public function execute(): RedirectResult
    {
        $redirect = $this->redirectFactory->create();

        try {
            $url = (string) $this->session->getPaymentSigningUrl();

            $this->checkoutSession->setResursFailureRedirectUrl($this->redirectReponse->getRefererUrl());

            if ($url !== '') {
                // Redirect to Resurs Bank signing page.
                $redirect->setUrl($url);
            } else {
                // Redirect to success page.
                $redirect->setPath('checkout/onepage/success');
            }
        } catch (Exception $e) {
            $this->log->exception($e);

            throw $e;
        }

        return $redirect;
    }
}
