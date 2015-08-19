<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 *
 *
 */
class RemoveParenthesesTextViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Removes any text inside a parentheses including the parentheses
     *
     * @param string $content Content string
     * @return string
     */
    public function render($content = NULL) {
        if ($content === NULL) {
            $content = $this->renderChildren();
        }

        return preg_replace("/\([^)]+\)/","",$content);
    }

}
