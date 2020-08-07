<?php

namespace Slub\SlubFindExtend\ViewHelpers\Find;

/**
 *
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class MetaTagViewHelper extends AbstractViewHelper  {

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('property', 'string', 'meta tag property');
        $this->registerArgument('content', 'string', 'meta tag content');
    }

    /**
     * @return void
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        if (empty($arguments['property'])) {
          return;
        }

        if (!empty($arguments['content'])) {
            $content = $arguments['content'];
        } else {
            $content = $renderChildrenClosure();
        }

        $metaTag = '<meta property="'.$arguments['property'].'" content="' . $content . '">';

        $GLOBALS['TSFE']->additionalHeaderData[$arguments['property']] = $metaTag;

        return;
    }
}