<?php

namespace Slub\FindSlub\ViewHelpers\Data;
use Solarium\QueryType\Select\Result\Facet\Field;

/**
 * View Helper to return merge facets with active facets
 *
 * Usage examples are available in Private/Partials/Test.html.
 */
class MergeWithActiveFacetsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {


	/**
	 * Register arguments.
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('data', 'array', 'The data to test', FALSE, NULL);
		$this->registerArgument('key', 'string', 'The data to test', FALSE, NULL);
		$this->registerArgument('activeFacets', 'array', 'The data to test', FALSE, NULL);
	}

	/**
	 * @return boolean
	 */
	public function render() {

		/** @var Field $data */
		$data = $this->arguments['data'];

		if(count($this->arguments['activeFacets'][$this->arguments['key']])) {

			$mergedData = array('values' => $data->getValues());

			foreach($this->arguments['activeFacets'][$this->arguments['key']] as $activeKey => $activeValue) {

				if(!count($mergedData['values'][$activeKey])) {

					$mergedData['values'] = array_merge(array($activeKey => $activeValue), $mergedData['values']);

				}

			}

			return $mergedData;

		} else {
			return $data;
		}


	}

}

?>