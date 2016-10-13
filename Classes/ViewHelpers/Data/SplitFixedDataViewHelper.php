<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 * View Helper to split data by fixed length to an array
 * 
 * Usage examples are available in Private/Partials/Test.html.
 */
class SplitFixedDataViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {


	/**
	 * Register arguments.
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('string', 'string', 'The string to split into components', TRUE, NULL);
		$this->registerArgument('lengths', 'string', 'The lengths seperated, by an "," to seperate the string', TRUE);
        $this->registerArgument('placeholder', 'string', 'The string that is used to mark a offset as not used', FALSE, '|');
	}


	
	/**
	 * @return array
	 */
	public function render() {

	    $string = $this->arguments['string'];
        if ($string === NULL) {
            $string = $this->renderChildren();
        }

		$lengths = explode(',',$this->arguments['lengths']);

        if (sizeof($lengths) == 0) {
            return $lengths;
        } else {

            $splittedString = [];

            foreach ($lengths as $length) {

                if (substr($string, 0, 1) === $this->arguments['placeholder']) {
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

?>