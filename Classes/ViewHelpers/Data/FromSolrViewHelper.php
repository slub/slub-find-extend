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

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('find') . 'vendor/autoload.php');

/**
 * FromSolrViewHelper
 *
 * Gets a field value from a Solr record
 *
 */
class FromSolrViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * @var \Solarium\Client
	 */
	protected $solr;

	public function initialize() {
		$configuration = array(
			'endpoint' => array(
				'localhost' => array(
					'host' => $this->templateVariableContainer->get('settings')['connection']['host'],
					'port' => intval($this->templateVariableContainer->get('settings')['connection']['port']),
					'path' => $this->templateVariableContainer->get('settings')['connection']['path'],
					'timeout' => $this->templateVariableContainer->get('settings')['connection']['timeout'],
					'scheme' => $this->templateVariableContainer->get('settings')['connection']['scheme']
				)
			)
		);

		$this->solr = new \Solarium\Client($configuration);
	}

	/**
	 * Register arguments.
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('id', 'string', 'ID to fetch', TRUE);
		$this->registerArgument('idfield', 'string', 'Field to query id', TRUE);
	}

	/**
	 */
	public function render() {

		$query = $this->createQuery($this->arguments['id'], $this->arguments['idfield']);

		/** @var Result $resultSet */
		$resultSet = $this->solr->select($query);

		/** @var DocumentInterface $result */
		$results = $resultSet->getDocuments();

		$out = '';

		if($results) {

			foreach ($results as $result) {

				if ($this->templateVariableContainer->exists('solr')) {
					$this->templateVariableContainer->remove('solr');
				}
				$this->templateVariableContainer->add('solr', $result);
				$out .= $this->renderThenChild();
			}
		} else {
			$out .= $this->renderElseChild();
		}


		return $out;
	}

	/**
	 * Check configuration for shards and when found create Distributed Search
	 *
	 * @param \Solarium\QueryType\Select\Query\Query $query
	 */
	private function createQueryComponents(&$query) {

		// Shards

		if(count($this->templateVariableContainer->get('settings')['shards'])) {
			$distributedSearch = $query->getDistributedSearch();
			foreach($this->templateVariableContainer->get('settings')['shards'] as $name => $shard) {
				$distributedSearch->addShard($name, $shard);
			}
		}
	}

	/**
	 * Adds filter queries configured in TypoScript to $query.
	 *
	 * @param \Solarium\QueryType\Select\Query\Query $query
	 */
	private function addTypoScriptFilters ($query) {
		if (!empty($this->templateVariableContainer->get('settings')['additionalFilters'])) {
			foreach($this->templateVariableContainer->get('settings')['additionalFilters'] as $key => $filterQuery) {
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
	private function createQuery ($id, $idfield) {
		$query = $this->solr->createSelect();
		$this->addTypoScriptFilters($query);

		$query->setQuery($idfield.':'.$id);

		$this->createQueryComponents($query);

		$this->configuration['solarium'] = $query;

		return $this->configuration['solarium'];
	}
}