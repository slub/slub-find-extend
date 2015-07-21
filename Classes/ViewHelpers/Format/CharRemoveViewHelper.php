<?php

namespace Slub\FindSlub\ViewHelpers\Format;

/**
 *
 *
 */
class CharRemoveViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Removes chard from string
     *
     * @param string $content Content string
     * @param integer $start Positive or negative offset
     * @param string $chars Comma seperated list of chars
     * @return string
     */
    public function render($content = NULL, $chars = NULL) {
        if ($content === NULL) {
            $content = $this->renderChildren();
        }
        if ($chars !== NULL) {
            $chars = explode(',', $chars);
            return str_replace($chars, '', $content);
        }
        return $content;
    }

}
