<?php

namespace Slub\SlubFindExtend\Slots;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Slub\SlubFindExtend\Services\ElasticsearchLogService;
use Solarium\QueryType\Select\Result\Result;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Slot: Logs each find search request (query + result count) to Elasticsearch.
 *
 * Hooks into: Subugoe\Find\Controller\SearchController::indexActionBeforeRender
 */
class LogSearchQuery
{
    protected array $settings = [];

    protected ConfigurationManagerInterface $configurationManager;

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );
    }

    /**
     * Called via SignalSlot after Solr executed the search query.
     *
     * @param Result|null $resultSet  Passed by reference from SolrServiceProvider::getDefaultQuery()
     */
    public function index(&$resultSet): void
    {
        // No result set means an error occurred – nothing useful to log.
        if (!$resultSet instanceof Result) {
            return;
        }

        $loggerSettings = is_array($this->settings['elasticsearchLog'] ?? null)
            ? $this->settings['elasticsearchLog']
            : [];

        $enabled = (int)($loggerSettings['enabled'] ?? ($this->settings['elasticsearchLogEnabled'] ?? 0)) === 1;
        if (!$enabled) {
            return;
        }

        $numFound = (int) $resultSet->getNumFound();

        // Extract query arguments from current request.
        $requestArguments = GeneralUtility::_GPmerged('tx_find_find');
        $query = is_array($requestArguments['q'] ?? null) ? $requestArguments['q'] : ['default' => (string) ($requestArguments['q'] ?? '')];

        $elasticsearchUrl = (string)($loggerSettings['url'] ?? ($this->settings['elasticsearchLogUrl'] ?? ''));
        if ($elasticsearchUrl === '') {
            return;
        }
        
        $indexName = (string)($loggerSettings['index'] ?? ($this->settings['elasticsearchLogIndex'] ?? 'find-search-log'));
        $timeout = (float)($loggerSettings['timeout'] ?? 0.4);
        if ($timeout > 1) {
            $timeout = $timeout / 1000;
        }
        $timeout = max(0.3, min(0.5, $timeout));

        $responseTimeMs = $this->resolveResponseTimeMs($resultSet);

        $service = GeneralUtility::makeInstance(ElasticsearchLogService::class, $elasticsearchUrl, $indexName, $timeout);
        $service->logSearch($query, $numFound, $requestArguments, $responseTimeMs);
    }

    private function resolveResponseTimeMs(Result $resultSet): ?int
    {
        if (method_exists($resultSet, 'getResponse')) {
            $response = $resultSet->getResponse();
            if (is_object($response) && method_exists($response, 'getData')) {
                $data = $response->getData();
                if (is_array($data)) {
                    $qTime = $data['responseHeader']['QTime'] ?? null;
                    if (is_numeric($qTime)) {
                        return (int)round((float)$qTime);
                    }
                }
            }
        }

        return null;
    }
}
