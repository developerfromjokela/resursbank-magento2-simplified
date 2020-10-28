<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class ValidateSsn extends AbstractHelper
{
    /**
     * @inheritDoc
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Validates a swedish SSN or Org. nr.
     *
     * @param string $ssn
     * @param bool $isCompany
     * @param bool $allowEmptyId
     * @return bool
     */
    public function sweden(
        string $ssn,
        bool $isCompany,
        bool $allowEmptyId = false
    ): bool {
        return $isCompany ?
            $this->swedenOrg($ssn, $allowEmptyId) :
            $this->swedenSsn($ssn, $allowEmptyId);
    }

    /**
     * Validates a SSN for Sweden.
     *
     * @param string $num
     * @param bool $allowEmptyId
     * @return bool
     */
    public function swedenSsn(
        string $num,
        bool $allowEmptyId = false
    ): bool {
        return $allowEmptyId && $num === '' ?
            true :
            (boolean) preg_match(
                '/^(18\d{2}|19\d{2}|20\d{2}|\d{2})' .
                '(0[1-9]|1[0-2])' .
                '([0][1-9]|[1-2][0-9]|3[0-1])' .
                '(\-|\+)?([\d]{4})$/',
                $num
            );
    }

    /**
     * Validates an organisation number for Sweden.
     *
     * @param string $num
     * @param bool $allowEmptyId
     * @return bool
     */
    public function swedenOrg(
        string $num,
        bool $allowEmptyId = false
    ): bool {
        return $allowEmptyId && $num === '' ?
            true :
            (boolean) preg_match(
                '/^(16\d{2}|18\d{2}|19\d{2}|20\d{2}|\d{2})' .
                '(\d{2})(\d{2})(\-|\+)?([\d]{4})$/',
                $num
            );
    }
}
