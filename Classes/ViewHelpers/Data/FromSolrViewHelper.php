<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/***************************************************************
 *
 *  Copyright notice
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Query\Query as SelectQuery;
use Solarium\QueryType\Select\Result\Result;
use Solarium\QueryType\Update\Query\Document\DocumentInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

require_once(ExtensionManagementUtility::extPath('find') . 'vendor/autoload.php');

/**
 * FromSolrViewHelper
 *
 * Gets a field value from a Solr record
 *
 */
class FromSolrViewHelper extends AbstractViewHelper
{
    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var \Solarium\Client
     */
    protected static $solr = null;

    public function __construct(\Solarium\Client $solrClient)
    {
        static::$solr = $solrClient;
    }

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('query', 'mixed', 'Solr querystring or array of query fields and their query values.', true);
        $this->registerArgument('operator', 'string', 'Solr query operator.', false, 'AND');
        $this->registerArgument('sortField', 'string', 'Sort field.', false);
        $this->registerArgument('sortOrder', 'string', 'Sort order ("asc" or "desc").', false, 'asc');
        $this->registerArgument('rows', 'integer', 'Number of rows to be returned.', false);
        $this->registerArgument('start', 'integer', 'Number of leading documents to skip.', false);
        $this->registerArgument('fields', 'string', 'Fields to be returned, comma seperated if more than one field.', false);
        $this->registerArgument('numFoundOnly', 'integer', 'Return numFound only, do not fetch Documents.', false, false);
    }

    /**
     * @return string
     */
    public static function renderStatic(
        array                     $arguments,
        \Closure                  $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    )
    {
        $templateVariableContainer = $renderingContext->getVariableProvider();

        $solrClient = static::getSolariumClient($templateVariableContainer);

        switch (gettype($arguments['query'])) {
            case 'string':
                $query = static::createQuery($solrClient, $arguments['query'], $templateVariableContainer);
                break;
            case 'array':
                $query = static::createQuery($solrClient, implode(' ' . $arguments['operator'] . ' ', array_map(function ($k, $v) {
                    return $k . ':' . $v;
                }, array_keys($arguments['query']), array_values($arguments['query']))), $templateVariableContainer);
                break;
            default:
                $query = static::createQuery($solrClient, '*:*', $templateVariableContainer);
        }

        if (!is_null($arguments['sortField'])) {
            $query->addSort($arguments['sortField'], $arguments['sortOrder']);
        }

        if (!is_null($arguments['rows'])) {
            $query->setRows($arguments['rows']);
        }

        if (!is_null($arguments['start'])) {
            $query->setStart($arguments['start']);
        }

        if (!is_null($arguments['fields'])) {
            $query->clearFields();
            $query->addFields($arguments['fields']);
        }

        $solrClient->setOptions(static::getSolariumClientOptionsArray($templateVariableContainer, $query));

        /** @var Result $resultSet */
        $resultSet = static::$solr->select($query);

        /** @var DocumentInterface $result */
        $results = $resultSet->getDocuments();

        if ($results) {
            if ($templateVariableContainer->exists('numFound')) {
                $templateVariableContainer->remove('numFound');
            }
            $templateVariableContainer->add('numFound', $resultSet->getNumFound());

            if (!$arguments['numFoundOnly']) {
                if ($templateVariableContainer->exists('documents')) {
                    $templateVariableContainer->remove('documents');
                }
                $templateVariableContainer->add('documents', $results);
            }
        } else {
            if ($templateVariableContainer->exists('numFound')) {
                $templateVariableContainer->remove('numFound');
            }
            $templateVariableContainer->add('numFound', 0);

            if ($templateVariableContainer->exists('documents')) {
                $templateVariableContainer->remove('documents');
            }
            $templateVariableContainer->add('documents', NULL);
        }

        return $renderChildrenClosure();
    }

    /**
     * Check configuration for shards and when found create Distributed Search
     * @param Query $query
     */
    private static function createQueryComponents(&$query, &$templateVariableContainer)
    {

        // Shards
        if (is_array($templateVariableContainer->get('settings')['shards']) && count($templateVariableContainer->get('settings')['shards'])) {
            $distributedSearch = $query->getDistributedSearch();
            foreach ($templateVariableContainer->get('settings')['shards'] as $name => $shard) {
                $distributedSearch->addShard($name, $shard);
            }
        }
    }

    /**
     * Adds filter queries configured in TypoScript to $query.
     *
     * @param Query $query
     */
    private static function addTypoScriptFilters(&$query, &$templateVariableContainer)
    {
        if (!empty($templateVariableContainer->get('settings')['additionalFilters'])) {
            foreach ($templateVariableContainer->get('settings')['additionalFilters'] as $key => $filterQuery) {
                $query->createFilterQuery('additionalFilter-' . $key)
                    ->setQuery($filterQuery);
            }
        }
    }

    /**
     * Creates a query for a document
     *
     * @param \Solarium\Client $solrClient
     * @param string $query
     * @param VariableProviderInterface $templateVariableContainer
     * @return SelectQuery
     */
    private static function createQuery($solrClient, $query, &$templateVariableContainer)
    {
        $queryObject = $solrClient->createSelect();
        static::addTypoScriptFilters($queryObject, $templateVariableContainer);

        $queryObject->setQuery($query);

        static::createQueryComponents($queryObject, $templateVariableContainer);

        return $queryObject;
    }

    /**
     * @return \Solarium\Client
     */
    private static function getSolariumClient()
    {
        if (null === static::$solr) {
            static::$solr = GeneralUtility::makeInstance(\Solarium\Client::class);
        }

        return static::$solr;
    }

    private static function getSolariumClientOptionsArray(&$templateVariableContainer, $query)
    {
        $configuration = array(
            'endpoint' => array(
                'localhost' => array(
                    'host' => $templateVariableContainer->get('settings')['connection']['host'],
                    'port' => intval($templateVariableContainer->get('settings')['connection']['port']),
                    'path' => $templateVariableContainer->get('settings')['connection']['path'],
                    'timeout' => $templateVariableContainer->get('settings')['connection']['timeout'],
                    'scheme' => $templateVariableContainer->get('settings')['connection']['scheme']
                )
            ),
            'solarium' => $query
        );

        return $configuration;
    }
}
