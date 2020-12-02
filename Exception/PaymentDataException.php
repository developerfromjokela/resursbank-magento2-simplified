<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * Indicates a problem with the data being supplied or taken from a payment.
 */
class PaymentDataException extends LocalizedException
{

}
