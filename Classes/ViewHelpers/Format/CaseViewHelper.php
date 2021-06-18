<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 *
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CaseViewHelper extends AbstractViewHelper
{

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('content', 'string', 'Content string', TRUE, NULL);
        $this->registerArgument('mode', 'string', 'lower or upper', TRUE, NULL);
   }

    /**
     * Changes case of string
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        $content = $arguments['content'];
        if ($arguments['content'] === NULL) {
            $content = $renderChildrenClosure();
        }
        $content = trim($content);
        if ($arguments['mode'] !== NULL) {
            switch ($arguments['mode']) {
                case 'lower':
                    return strtolower($content);
                    break;
                case 'upper':
                    return strtoupper($content);
                    break;
                case 'ucfirst':
                    return ucfirst($content);
                    break;
                default:
                    return $content;
            }
        }
        return $content;
    }

}
