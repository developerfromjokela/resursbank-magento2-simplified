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
use Resursbank\Core\Helper\Session as CoreSession;

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
     * @var CoreSession
     */
    private CoreSession $coreSession;

    /**
     * @var Log
     */
    private Log $log;

    /**
     * @param RedirectFactory $redirectFactory
     * @param Session $session
     * @param Log $log
     * @param CoreSession $coreSession
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        Session $session,
        Log $log,
        CoreSession $coreSession
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->session = $session;
        $this->coreSession = $coreSession;
        $this->log = $log;
    }

    /**
     * @inheritDoc
     *
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

            $this->coreSession->useRefererAsFailureRedirectUrl();

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
