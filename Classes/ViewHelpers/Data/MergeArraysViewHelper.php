<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;


class MergeArraysViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {


	/**
	 * Register arguments.
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('arrayOne', 'array', 'The first array.', TRUE, array());
		$this->registerArgument('arrayTwo', 'array', 'The second array', TRUE, array());
	}

	/**
	 * @return array
	 */
	public function render() {
		$arrayOne = $this->arguments['arrayOne'];
		$arrayTwo = $this->arguments['arrayTwo'];

		if ($arrayOne !== NULL && $arrayTwo !== NULL) {
			return array_merge($arrayOne, $arrayTwo);
		}

		if ($arrayOne === NULL) {
			return $arrayTwo;
		} else if ($arrayTwo === NULL) {
			return $arrayOne;
		}

		return NULL;
	}

}

?>