<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 *
 *
 */
class CaseViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Changes case of string
     *
     * @param string $content Content string
     * @param string $mode lower or upper
     * @return string
     */
    public function render($content = NULL, $mode = NULL) {
        if ($content === NULL) {
            $content = $this->renderChildren();
        }
        $content = trim($content);
        if ($mode !== NULL) {
            switch ($mode) {
                case 'lower':
                    return strtolower($content);
                    break;
                case 'upper':
                    return strtoupper($content);
                    break;
                default:
                    return $content;
            }
        }
        return $content;
    }

}
