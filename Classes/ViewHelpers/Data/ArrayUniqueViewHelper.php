<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**

 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ArrayUniqueViewHelper extends AbstractViewHelper {

	/**
	 * Register arguments.
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('array', 'string', 'The array to filter', FALSE, NULL);
	}

	/**
	 * @return array
	 */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $array = $arguments['array'];

        if ($array === NULL) {
            $array = $renderChildrenClosure();
        }

        if(!is_array($array)) return NULL;

		return array_unique($array);
	}

}

?>
