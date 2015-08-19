<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 *
 *
 */
class MinifyViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Removes \t and \n from string
     *
     * @param string $content Content string
     * @return string
     */
    public function render($content = NULL) {
        if ($content === NULL) {
            $content = $this->renderChildren();
        }

        return str_replace(array("\n", "\t"), '', $content);
    }

}
