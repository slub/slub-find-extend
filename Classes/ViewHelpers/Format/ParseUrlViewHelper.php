<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 * Gets splitted URL via parse_url
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ParseUrlViewHelper extends AbstractViewHelper {

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('url', 'string', 'URL string', TRUE, NULL);
    }

    /**
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        $url = parse_url($arguments['url']);

        $url['ext'] = pathinfo($url['path'], PATHINFO_EXTENSION);

        return $url;
    }

}
