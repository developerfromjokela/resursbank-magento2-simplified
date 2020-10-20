<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Api extends AbstractHelper
{
    /**
     * Customer type for company.
     *
     * @var string
     */
    const CUSTOMER_TYPE_COMPANY = 'LEGAL';

    /**
     * Customer type for private citizens.
     *
     * @var string
     */
    const CUSTOMER_TYPE_PRIVATE = 'NATURAL';

    /**
     * @inheritDoc
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }
}
