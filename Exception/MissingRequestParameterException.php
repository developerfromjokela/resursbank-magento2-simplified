<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * Indicates a problem with supplied data to a function, server call etc.
 *
 * @package Resursbank\Core\Exception
 */
class MissingRequestParameterException extends LocalizedException
{

}
