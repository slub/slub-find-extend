<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 * Removes any text inside a parentheses including the parentheses
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class RemoveParenthesesTextViewHelper extends AbstractViewHelper
{

    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('content', 'string', 'Content string', FALSE);
        $this->registerArgument('limit', 'int', 'Limit replacments', FALSE, -1);
    }

    /**
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        $content = $arguments['content'];

        if ($content === NULL) {
            $content = $renderChildrenClosure();
        }

        return preg_replace("/\([^)]+\)/","",$content, $arguments['limit']);
    }

}
