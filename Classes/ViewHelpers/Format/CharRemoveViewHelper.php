<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 *
 *
 */
class CharRemoveViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Removes chard from string
     *
     * @param string $content Content string
     * @param string $chars Comma seperated list of chars
     * @return string
     */
    public function render($content = NULL, $chars = NULL) {
        if ($content === NULL) {
            $content = $this->renderChildren();
        }
        $content = trim($content);
        if ($chars !== NULL) {
            $chars = explode(',', $chars);
            return trim(str_replace($chars, '', $content));
        }
        return $content;
    }

}
