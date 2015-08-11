<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 * Gets splitted URL via parse_url
 *
 */
class ParseUrlViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Splits a string with parse_url
     *
     * @param string $url URL string
     * @return array
     */
    public function render($url = NULL) {
        return parse_url($url);
    }

}
