<?php

namespace Slub\SlubFindExtend\ViewHelpers\Logic;

/*
 * This file is part of the FluidTYPO3/Vhs project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

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
class EqualsViewHelper extends AbstractConditionViewHelper {

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('string', 'string', 'The string to check against', TRUE, NULL);
        $this->registerArgument('test', 'string', 'The string to check', TRUE, NULL);
    }

    /**
     * @param array $arguments
     * @return bool
     */
    protected static function evaluateCondition($arguments = null) {

        $string = $arguments['string'];
        $test = $arguments['test'];

        if (FALSE !== ($string === $test)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

}
