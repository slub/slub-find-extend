<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**

 */
class ArrayUniqueViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Register arguments.
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('array', 'string', 'The array to filter', FALSE, NULL);
	}

	/**
	 * @return array
	 */
	public function render() {
        $array = $this->arguments['array'];

        if ($array === NULL) {
            $array = $this->renderChildren();
        }

        if(!is_array($array)) return NULL;

		return array_unique($array);
	}

}

?>
