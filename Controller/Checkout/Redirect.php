<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Controller\Checkout;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;
use Magento\Framework\Controller\Result\RedirectFactory;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Session;

/**
 * Redirect to signing page at Resurs Bank.
 *
 * NOTE: Controllers should not extend Magento\Framework\App\Action\Action
 * anymore, but should instead embrace composition by implementing an interface,
 * such as Magento\Framework\App\Action\HttpPostActionInterface.
 *
 * We are, however, forced to extend the Action class here. When Magento
 * redirects the customer from the checkout page to the success page, they do
 * so by replacing the URL in the browser. This will (as of Magento 2.4.1)
 * make it impossible to reach a controller that only implements an interface.
 */
class Redirect extends Action
{
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Log
     */
    private $log;

    /**
     * @param Context $context
     * @param RedirectFactory $redirectFactory
     * @param Session $session
     * @param Log $log
     */
    public function __construct(
        Context $context,
        RedirectFactory $redirectFactory,
        Session $session,
        Log $log
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->session = $session;
        $this->log = $log;

        parent::__construct($context);
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
            /* @noinspection PhpUndefinedMethodInspection */
            $url = (string) $this->session->getPaymentSigningUrl();

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
