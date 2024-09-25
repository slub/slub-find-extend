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
        $this->registerArgument('array', 'array', 'The data to access', true, null);
        $this->registerArgument('type', 'string', 'The type to sort ("value" => sort()) otherwise ksort())', false, null);
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

        // Parses sort type constant name to corresponding int value, eg. SORT_NATURAL => 6
        if(is_string($arguments['flag'])) {
            $arguments['flag'] = constant($arguments['flag']);
        }

        if($arguments['type'] == 'value') {
            sort($array, $arguments['flag']);
        } else {
            ksort($array, $arguments['flag']);
        }

        return $array;
    }
}
