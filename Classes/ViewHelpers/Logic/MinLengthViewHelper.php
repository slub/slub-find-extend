<?php

namespace Slub\SlubFindExtend\ViewHelpers\Logic;

/*
 * Checks minimum length of string
 */

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper;


class MinLengthViewHelper extends AbstractConditionViewHelper {

    /**
     * Register arguments.
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('string', 'string', 'The string to check against minlength', TRUE, NULL);
        $this->registerArgument('length', 'int', 'The minlength to check', TRUE, NULL);
    }

    /**
     * Render method
     *
     * @param string $string
     * @param int $length
     * @return string
     */
    public function render() {

        $string = $this->arguments['json'];
        $length = $this->arguments['length'];

        if($length === NULL) { return TRUE; }

        if (FALSE !== (strlen($string) >= $length) ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

}
