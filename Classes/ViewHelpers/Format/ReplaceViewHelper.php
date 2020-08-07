<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 * Replaces chars inside content
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ReplaceViewHelper extends AbstractViewHelper  {

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
        $this->registerArgument('content', 'string', 'Content', FALSE, NULL);
        $this->registerArgument('needle', 'string', 'Needle', FALSE, NULL);
        $this->registerArgument('replace', 'string', 'Replace', FALSE, NULL);
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

		if ($content === NULL) {
			$content = $renderChildrenClosure();
		}

		if ($content && $needle && $replace && (count($content) > 0) && (count($needle) > 0) && (count($replace) > 0)){
			return str_replace($needle, $replace, $content);
		} else {
			return '';
		}
	}

}
