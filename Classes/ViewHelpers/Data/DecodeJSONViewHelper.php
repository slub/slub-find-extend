<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**

 */
class DecodeJSONViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Register arguments.
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('json', 'string', 'The json string to decode', FALSE, NULL);
	}

	/**
	 * @return array
	 */
	public function render() {
		$json = json_decode($this->arguments['json'], true);
		return $json;
	}

}

?>