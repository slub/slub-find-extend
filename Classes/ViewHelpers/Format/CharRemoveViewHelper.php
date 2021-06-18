<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 * Removes chars from string
 *
 */
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CharRemoveViewHelper extends AbstractViewHelper
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
        $this->registerArgument('chars', 'string', 'Comma seperated list of chars', TRUE, NULL);
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
        $chars = $arguments['chars'];

        if ($content === NULL) {
            $content = $renderChildrenClosure();
        }
        $content = trim($content);
        if ($chars !== NULL) {
            $chars = explode(',', $chars);
            return trim(str_replace($chars, '', $content));
        }
        return $content;
    }

}
