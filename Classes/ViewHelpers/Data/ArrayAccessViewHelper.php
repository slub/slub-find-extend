<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**

 */
class ArrayAccessViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Register arguments.
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('data', 'array', 'The data to access', FALSE, NULL);
		$this->registerArgument('index', 'int', 'The index to acces', FALSE, NULL);
	}

	/**
	 * @return boolean
	 */
	public function render() {
		$data = $this->arguments['data'];
		return $data[$this->arguments['index']];
	}

}

?>