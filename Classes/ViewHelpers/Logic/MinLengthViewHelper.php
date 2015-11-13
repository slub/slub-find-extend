<?php

namespace Slub\SlubFindExtend\ViewHelpers\Logic;

/*
 * Checks minimum length of string
 */

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * ### Condition: String contains substring
 *
 * Condition ViewHelper which renders the `then` child if provided
 * string $haystack contains provided string $needle.
 *
 * @author Björn Fromme <fromme@dreipunktnull.com>, dreipunktnull
 * @package Vhs
 * @subpackage ViewHelpers\Condition\String
 */
class MinLengthViewHelper extends AbstractConditionViewHelper {

    /**
     * Render method
     *
     * @param string $string
     * @param int $length
     * @return string
     */
    public function render($string, $length = NULL) {

        if($length === NULL) { return TRUE; }

        if (FALSE !== (strlen($string) >= $length) ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

}
