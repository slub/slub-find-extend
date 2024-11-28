<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ArrayPluckViewHelper extends AbstractViewHelper
{
    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('array', 'array', 'The array to pluck values from', true);
        $this->registerArgument('pluck', 'string|array', 'String or array to remove', true);
        $this->registerArgument('flattenArray', 'boolean', 'Flatten associative Array', true);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext

     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $array = $arguments['array'];
        $pluck = $arguments['pluck'];
        $flattenArray = $arguments['flattenArray'];

        if($flattenArray) {
            $array = array_values($array);
        }

        if (!is_array($pluck)) {
            $pluck = [$pluck];
        }

        $array = array_diff($array, $pluck);

        return $array;
    }
}
