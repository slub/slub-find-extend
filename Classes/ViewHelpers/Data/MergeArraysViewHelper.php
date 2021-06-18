<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class MergeArraysViewHelper extends AbstractViewHelper
{


	/**
	 * Register arguments.
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('arrayOne', 'array', 'The first array.', TRUE, array());
		$this->registerArgument('arrayTwo', 'array', 'The second array', TRUE, array());
	}

    /**
     * @return mixed
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
		$arrayOne = $arguments['arrayOne'];
		$arrayTwo = $arguments['arrayTwo'];

		if ($arrayOne !== NULL && $arrayTwo !== NULL) {
			return array_merge($arrayOne, $arrayTwo);
		}

		if ($arrayOne === NULL) {
			return $arrayTwo;
		} else if ($arrayTwo === NULL) {
			return $arrayOne;
		}

		return NULL;
	}

}

?>
