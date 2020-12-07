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

namespace Resursbank\Simplified\Controller\Checkout;

use Exception;
use Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
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
class Redirect extends Action implements HttpPostActionInterface
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
    public function execute()
    {
        $redirect = $this->redirectFactory->create();

        try {
            $url = (string) $this->session->getPaymentSigningUrl();

            if ($url !== '') {
                // Redirect to Resurs Bank signing page.
                $redirect->setUrl($url);
            } else {
                // Redirect to Magento's success page.
                $redirect->setPath('checkout/onepage/success');
            }
        } catch (Exception $e) {
            $this->log->error($e);

            throw $e;
        }

        return $redirect;
    }
}
