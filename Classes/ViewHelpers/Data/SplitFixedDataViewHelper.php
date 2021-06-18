<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 * View Helper to split data by fixed length to an array
 *
 * Usage examples are available in Private/Partials/Test.html.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class SplitFixedDataViewHelper extends AbstractViewHelper
{

	/**
	 * Register arguments.
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('string', 'string', 'The string to split into components', FALSE, NULL);
		$this->registerArgument('lengths', 'string', 'The lengths seperated, by an "," to seperate the string', FALSE);
        $this->registerArgument('placeholder', 'string', 'The string that is used to mark a offset as not used', FALSE, '|');
	}

    /**
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

	    $string = $arguments['string'];
        if ($string === NULL) {
            $string = $renderChildrenClosure();
        }

		$lengths = explode(',',$arguments['lengths']);

        if (sizeof($lengths) == 0) {
            return $lengths;
        } else {

            $splittedString = [];

            foreach ($lengths as $length) {

                if (substr($string, 0, 1) === $arguments['placeholder']) {
                    $splittedString[] = '';
                    $string = substr($string, 1);
                } else {
                    $splittedString[] = substr($string, 0, $length);
                    $string = substr($string, $length);
                }

            }

            return $splittedString;

        }
	}

}
