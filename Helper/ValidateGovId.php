<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use function preg_match;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class ValidateGovId extends AbstractHelper
{
    /**
     * Validates government ID.
     *
     * @param string $govId
     * @param bool $isCompany
     * @param string $country
     * @param bool $allowEmptyId
     * @return bool
     */
    public function validate(
        string $govId,
        bool $isCompany,
        string $country,
        bool $allowEmptyId = false
    ): bool {
        $result = false;

        if ($country === 'SE') {
            $result = $this->sweden($govId, $isCompany, $allowEmptyId);
        } elseif ($country === 'NO') {
            $result = $this->norway($govId, $isCompany, $allowEmptyId);
        } elseif ($country === 'FI') {
            $result = $this->finland($govId, $isCompany, $allowEmptyId);
        } elseif ($country === 'DK') {
            $result = $this->denmark($govId, $allowEmptyId);
        }

        return $result;
    }

    /**
     * Validates a Swedish government ID (SSN or Org. nr.).
     *
     * @param string $govId
     * @param bool $isCompany
     * @param bool $allowEmptyId
     * @return bool
     */
    public function sweden(
        string $govId,
        bool $isCompany,
        bool $allowEmptyId = false
    ): bool {
        return $isCompany ?
            $this->swedenOrg($govId, $allowEmptyId) :
            $this->swedenSsn($govId, $allowEmptyId);
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
        return ($allowEmptyId && $num === '') || (bool) preg_match(
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
        return ($allowEmptyId && $num === '') || (bool) preg_match(
            '/^(16\d{2}|18\d{2}|19\d{2}|20\d{2}|\d{2})' .
            '(\d{2})(\d{2})(\-|\+)?([\d]{4})$/',
            $num
        );
    }

    /**
     * Validates a Norwegian government ID (SSN or Org. nr.)
     *
     * @param string $govId
     * @param bool $isCompany
     * @param bool $allowEmptyId
     * @return bool
     */
    public function norway(
        string $govId,
        bool $isCompany,
        bool $allowEmptyId = false
    ): bool {
        return $isCompany ?
            $this->norwayOrg($govId, $allowEmptyId) :
            $this->norwaySsn($govId, $allowEmptyId);
    }

    /**
     * Validates a SSN for Norway.
     *
     * @param string $num
     * @param bool $allowEmptyId
     * @return bool
     */
    public function norwaySsn(
        string $num,
        bool $allowEmptyId = false
    ): bool {
        return ($allowEmptyId && $num === '') || (bool) preg_match(
            '/^([0][1-9]|[1-2][0-9]|3[0-1])(0[1-9]|1[0-2])(\d{2})(\-)?' .
            '([\d]{5})$/',
            $num
        );
    }

    /**
     * Validates an organisation number for Norway.
     *
     * @param string $num
     * @param bool $allowEmptyId
     * @return bool
     */
    public function norwayOrg(
        string $num,
        bool $allowEmptyId = false
    ): bool {
        return ($allowEmptyId && $num === '') || (bool) preg_match(
            '/^(16\d{2}|18\d{2}|19\d{2}|20\d{2}|\d{2})(\d{2})(\d{2})' .
            '(\-|\+)?([\d]{4})$/',
            $num
        );
    }

    /**
     * Validates a Finish government ID (SSN or Org. nr.).
     *
     * @param string $govId
     * @param bool $isCompany
     * @param bool $allowEmptyId
     * @return bool
     */
    public function finland(
        string $govId,
        bool $isCompany,
        bool $allowEmptyId = false
    ): bool {
        return $isCompany ?
            $this->finlandOrg($govId, $allowEmptyId) :
            $this->finlandSsn($govId, $allowEmptyId);
    }

    /**
     * Validates a SSN for Finland.
     *
     * @param string $num
     * @param bool $allowEmptyId
     * @return bool
     */
    public function finlandSsn(
        string $num,
        bool $allowEmptyId = false
    ): bool {
        return ($allowEmptyId && $num === '') || (bool) preg_match(
            '/^([\d]{6})[\+-A]([\d]{3})' .
            '([0123456789ABCDEFHJKLMNPRSTUVWXY])$/',
            $num
        );
    }

    /**
     * Validates an organisation number for Finland.
     *
     * @param string $num
     * @param bool $allowEmptyId
     * @return bool
     */
    public function finlandOrg(
        string $num,
        bool $allowEmptyId = false
    ): bool {
        return ($allowEmptyId && $num === '') || (bool) preg_match(
            '/^((\d{7})(\-)?\d)$/',
            $num
        );
    }

    /**
     * Validates a Danish government ID (SSN or Org. nr.).
     *
     * @param string $govId
     * @param bool $allowEmptyId
     * @return bool
     */
    public function denmark(
        string $govId,
        bool $allowEmptyId = false
    ): bool {
        return $this->denmarkSsn($govId, $allowEmptyId);
    }

    /**
     * Validates a SSN for Denmark.
     *
     * @param string $num
     * @param bool $allowEmptyId
     * @return bool
     */
    public function denmarkSsn(
        string $num,
        bool $allowEmptyId = false
    ): bool {
        return ($allowEmptyId && $num === '') || (bool) preg_match(
            '/^((3[0-1])|([1-2][0-9])|(0[1-9]))((1[0-2])|(0[1-9]))' .
            '(\d{2})(\-)?([\d]{4})$/',
            $num
        );
    }
}
