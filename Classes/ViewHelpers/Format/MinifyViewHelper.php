<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 * Removes \t and \n from string
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class MinifyViewHelper extends AbstractViewHelper
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
        $this->registerArgument('content', 'string', 'Content string', FALSE, NULL);
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

        return str_replace(array("\n", "\t"), '', $content);
    }

}
