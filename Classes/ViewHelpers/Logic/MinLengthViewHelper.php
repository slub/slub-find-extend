<?php

namespace Slub\SlubFindExtend\ViewHelpers\Logic;

/*
 * Checks minimum length of string
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

class MinLengthViewHelper extends AbstractConditionViewHelper
{
    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Register arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('string', 'string', 'The string to check against minlength', true, null);
        $this->registerArgument('length', 'int', 'The minlength to check', false, null);
    }

    /**
     * evaluate method
     * @param array $arguments
     * @return boolean
     */
    protected static function evaluateCondition($arguments = null)
    {
        $string = $arguments['string'];
        $length = $arguments['length'];

        if ($length === null) {
            return true;
        }

        if (false !== (strlen($string) >= $length)) {
            return true;
        } else {
            return false;
        }
    }
}
