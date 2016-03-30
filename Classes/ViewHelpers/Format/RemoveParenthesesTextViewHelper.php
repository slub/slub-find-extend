<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 *
 *
 */
class RemoveParenthesesTextViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('content', 'string', 'Content string', FALSE);
        $this->registerArgument('limit', 'int', 'Limit replacments', FALSE, -1);
    }


    /**
     * Removes any text inside a parentheses including the parentheses
     *
     * @return string
     */
    public function render() {

        $content = $this->arguments['content'];

        if ($content === NULL) {
            $content = $this->renderChildren();
        }

        return preg_replace("/\([^)]+\)/","",$content, $this->arguments['limit']);
    }

}
