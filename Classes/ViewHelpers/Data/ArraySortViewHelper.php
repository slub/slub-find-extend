<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**

 */
class ArraySortViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Register arguments.
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('array', 'array', 'The data to access', FALSE, NULL);
		$this->registerArgument('flag', 'string', 'The flags to sort', FALSE, NULL);
	}

	/**
	 * @return boolean
	 */
	public function render() {
        $array = $this->arguments['array'];

        if ($array === NULL) {
            $array = $this->renderChildren();
        }

        if(!is_array($array)) return NULL;

        ksort($array, $this->arguments['flag']);

        return $array;
	}

}

?>
