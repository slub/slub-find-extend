<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**

 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ArraySortViewHelper extends AbstractViewHelper
{
    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('array', 'array', 'The data to access', false, null);
        $this->registerArgument('flag', 'string', 'The flags to sort', false, null);
    }

    /**
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $array = $arguments['array'];

        if ($array === null) {
            $array = $renderChildrenClosure();
        }

        if (!is_array($array)) {
            return null;
        }

        ksort($array, $arguments['flag']);

        return $array;
    }
}
