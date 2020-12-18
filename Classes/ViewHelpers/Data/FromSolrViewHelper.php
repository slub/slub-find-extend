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

use Solarium\QueryType\Select\Result\Result;
use Solarium\QueryType\Update\Query\Document\DocumentInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FromSolrViewHelper
 *
 * Gets a field value from a Solr record
 *
 */
class FromSolrViewHelper extends AbstractViewHelper {

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

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('query', 'mixed', 'Solr querystring or array of query fields and their query values.', TRUE);
        $this->registerArgument('operator', 'string', 'Solr query operator.', FALSE, 'AND');
        $this->registerArgument('sortField', 'string', 'Sort field.', FALSE);
        $this->registerArgument('sortOrder', 'string', 'Sort order ("asc" or "desc").', FALSE, 'asc');
        $this->registerArgument('rows', 'integer', 'Number of rows to be returned.', FALSE);
        $this->registerArgument('fields', 'string', 'Fields to be returned, comma seperated if more than one field.', FALSE);
    }

    /**
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        echo "evaluateC".static::class;

        $templateVariableContainer = $renderingContext->getVariableProvider();

        $solrClient = static::getSolariumClient($templateVariableContainer);

        switch(gettype($arguments['query'])) {
            case 'string':
                $query = static::createQuery($solrClient, $arguments['query'], $templateVariableContainer);
                break;
            case 'array':
                $query = static::createQuery($solrClient, implode(' ' . $arguments['operator'] . ' ', array_map( function($k,$v) { return $k . ':' . $v; }, array_keys($arguments['query']), array_values($arguments['query']))), $templateVariableContainer);
                break;
            default:
                $query = static::createQuery($solrClient, '*:*', $templateVariableContainer);
        }

        if(!is_null($arguments['sortField'])) {
            $query->addSort($arguments['sortField'], $arguments['sortOrder']);
        }

        if(!is_null($arguments['rows'])) {
            $query->setRows($arguments['rows']);
        }

        if(!is_null($arguments['fields'])) {
            $query->clearFields();
            $query->addFields($arguments['fields']);
        }

        $solrClient->setOptions(static::getSolariumClientOptionsArray($templateVariableContainer, $query));

        /** @var Result $resultSet */
        $resultSet = static::$solr->select($query);

        /** @var DocumentInterface $result */
        $results = $resultSet->getDocuments();

        if($results) {
            if ($templateVariableContainer->exists('documents')) {
                $templateVariableContainer->remove('documents');
            }
            $templateVariableContainer->add('documents', $results);
        }

        return $renderChildrenClosure();
    }

    /**
     * Check configuration for shards and when found create Distributed Search
     * @param \Solarium\QueryType\Select\Query\Query $query
     */
    private static function createQueryComponents(&$query, &$templateVariableContainer) {

        // Shards
        if(is_array($templateVariableContainer->get('settings')['shards']) && count($templateVariableContainer->get('settings')['shards'])) {
            $distributedSearch = $query->getDistributedSearch();
            foreach($templateVariableContainer->get('settings')['shards'] as $name => $shard) {
                $distributedSearch->addShard($name, $shard);
            }
        }
    }

    /**
     * Adds filter queries configured in TypoScript to $query.
     *
     * @param \Solarium\QueryType\Select\Query\Query $query
     */
    private static function addTypoScriptFilters (&$query, &$templateVariableContainer) {
        if (!empty($templateVariableContainer->get('settings')['additionalFilters'])) {
            foreach($templateVariableContainer->get('settings')['additionalFilters'] as $key => $filterQuery) {
                $query->createFilterQuery('additionalFilter-' . $key)
                    ->setQuery($filterQuery);
            }
        }
    }

    /**
     * Creates a query for a document
     *
     * @param string $id the document id
     * @param string $idfield the document id field
     * @return \Solarium\QueryType\Select\Query\Query
     */
    private static function createQuery ($solrClient, $query, &$templateVariableContainer) {

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
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            static::$solr = $objectManager->get(\Solarium\Client::class);
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
