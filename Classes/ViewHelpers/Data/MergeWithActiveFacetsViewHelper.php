<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 * View Helper to return merge facets with active facets
 *
 * Usage examples are available in Private/Partials/Test.html.
 */

use Solarium\QueryType\Select\Result\Facet\Field;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class MergeWithActiveFacetsViewHelper extends AbstractViewHelper {

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
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

		/** @var Field $data */
		$data = $arguments['data'];

		if(is_array($arguments['activeFacets'][$arguments['key']]) && count($arguments['activeFacets'][$arguments['key']])) {

			$mergedData = array('values' => $data->getValues());

			foreach($arguments['activeFacets'][$arguments['key']] as $activeKey => $activeValue) {

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