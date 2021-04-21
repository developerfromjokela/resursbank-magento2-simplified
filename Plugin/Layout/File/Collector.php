<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Simplified\Plugin\Layout\File;

use Exception;
use Magento\Framework\View\File;
use Magento\Framework\View\Layout\File\Collector\Aggregated;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Simplified\Helper\Config;
use Resursbank\Simplified\Helper\Log;
use function strpos;

/**
 * This plugin will exclude our overriding layout file for checkout_index_index
 * if the module has been disabled.
 *
 * This plugin is necessary to ensure different API flows may be applied
 * individually in each store. Otherwise the extending checkout_index_index.xml
 * files might conflict with each other.
 */
class Collector
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Log
     */
    private $log;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        Log $log
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->log = $log;
    }

    /**
     * If this module is disabled, remove our checkout_index_index.xml file
     * from the collection of XML files assembled by Magento.
     *
     * @param Aggregated $subject
     * @param array<string, mixed> $result
     * @return array<string, File>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterGetFiles(
        Aggregated $subject,
        array $result
    ): array {
        try {
            $storeCode = $this->storeManager->getStore()->getCode();

            if (!$this->config->isActive($storeCode)) {
                foreach ($result as $key => $file) {
                    if ($this->isOurLayoutFile($file)) {
                        unset($result[$key]);
                    }
                }
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * Check if provided layout file is the extending checkout_index_index.xml
     * file that belongs to this module.
     *
     * @param File $file
     * @return bool
     */
    protected function isOurLayoutFile(
        File $file
    ): bool {
        return (
            $file->getModule() === 'Resursbank_Simplified' &&
            strpos($file->getFileIdentifier(), 'checkout_index_index.xml')
        );
    }
}
