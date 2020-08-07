<?php
namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 * IsArrayEmptyViewHelper
 *
 * Checks if an array is empty.
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

class IsArrayEmptyViewHelper extends AbstractConditionViewHelper {

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('array', 'array', 'The array to check', TRUE, NULL);
    }

    /**
     * @return boolean
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $array = $arguments['array'];
		return empty($array);
    }
}
