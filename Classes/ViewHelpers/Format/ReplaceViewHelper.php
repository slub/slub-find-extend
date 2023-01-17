<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 * Replaces chars inside content
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ReplaceViewHelper extends AbstractViewHelper
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
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('content', 'string', 'Content', false, null);
        $this->registerArgument('needle', 'string', 'Needle', false, null);
        $this->registerArgument('replace', 'string', 'Replace', false, '');
        $this->registerArgument('regexp', 'booblean', 'Regexp', false, false);
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
        $needle = $arguments['needle'];
        $replace = $arguments['replace'];

        if ($content === null) {
            $content = $renderChildrenClosure();
        }

        if (!empty($content) && !empty($needle)) {
            if($arguments['regexp']) {
                return preg_replace($needle, $replace, $content);
            } else {
                return str_replace($needle, $replace, $content);
            }
            
        } else {
            return '';
        }
    }
}
