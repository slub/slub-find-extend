<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 * IsNullOrZeroViewHelpe
 *
 * Checks if a value is null or the value 0.
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class IsNullOrZeroViewHelper extends AbstractViewHelper
{
    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'mixed', 'The value to check', true, null);
    }

    /**
     * @return boolean
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $value = $arguments['value'];
        return ($value === '0' || $value === 0 || $value === null || $value === '') ? true : false;
    }
}
