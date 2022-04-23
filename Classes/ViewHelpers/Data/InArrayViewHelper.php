<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class InArrayViewHelper extends AbstractViewHelper
{
    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('haystack', 'array', 'Array to search', true);
        $this->registerArgument('needle', 'string', 'Needle to search', true);
        $this->registerArgument('strict', 'boolean', 'Strict mode?', false, false);
    }

    /**
     * @return mixed
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        if (!is_array($arguments['haystack'])) {
            return false;
        }

        return in_array($arguments['needle'], $arguments['haystack'], $targuments['strict']);
    }
}
