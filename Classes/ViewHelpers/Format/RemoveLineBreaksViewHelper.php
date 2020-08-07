<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 * Removes line breaks inside content
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class RemoveLineBreaksViewHelper extends AbstractViewHelper  {

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
        $this->registerArgument('content', 'string', 'Content string', TRUE, NULL);
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

		if ($content && (strlen($content) > 0)){
			return str_replace(array("\r\n", "\n", "\r"), ' ', $content);
		} else {
			return '';
		}
	}

}
