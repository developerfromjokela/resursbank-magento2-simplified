<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Partpayment\Controller;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;
use Magento\Framework\Controller\Result\RedirectFactory;
use Resursbank\Core\Model\Api\Payment\Converter\QuoteConverter;
use Resursbank\Simplified\Helper\Log;
use Resursbank\Simplified\Helper\Session;

/**
 * Fetch HTML containing part payment option details.
 */
class FetchAddress implements HttpGetActionInterface
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
     * @var QuoteConverter
     */
    private $quoteConverter;

    /**
     * @param RedirectFactory $redirectFactory
     * @param Session $session
     * @param Log $log
     * @param QuoteConverter $quoteConverter
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        Session $session,
        Log $log,
        QuoteConverter $quoteConverter
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->session = $session;
        $this->log = $log;
        $this->quoteConverter = $quoteConverter;
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
        $this->quoteConverter->convert($this->session->getQuote());
        $test = 'ad';
        
        $redirect = $this->redirectFactory->create();

        try {
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
