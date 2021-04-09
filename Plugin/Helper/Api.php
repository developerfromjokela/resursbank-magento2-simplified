<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Helper;

use Resursbank\Core\Helper\Version;
use Resursbank\Core\Helper\Api as Subject;

/**
 * Appends version assigned in module composer.json to API call user agent.
 */
class Api
{
    /**
     * @var Version
     */
    private $version;

    /**
     * @param Version $version
     */
    public function __construct(
        Version $version
    ) {
        $this->version = $version;
    }

    /**
     * @param Subject $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterGetUserAgent(
        Subject $subject,
        string $result
    ): string {
        return $result . sprintf(
            ' | Resursbank_Simplified %s',
            $this->version->getComposerVersion('Resursbank_Simplified')
        );
    }
}
