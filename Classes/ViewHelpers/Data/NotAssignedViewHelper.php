<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;


class NotAssignedViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	private $notAssignedStrings = ['No subject assigned', 'not assigned', 'Not assigned'];

	/**
	 * Register arguments.
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('data', 'array|string', 'The data to test', FALSE, NULL);
	}

	/**
	 * @return boolean
	 */
	public function render() {

		$data = $this->arguments['data'];

		if(in_array($data, $this->notAssignedStrings)) {
			return false;
		}

		if(is_array($data)) {
			if(in_array($data[0], $this->notAssignedStrings)) {
				return false;
			}
		}

		return true;

	}

}

?>