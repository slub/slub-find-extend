<?php

namespace Slub\FindSlub\ViewHelpers\Find;

/**
 * Class HoldingStatusJsonViewHelper
 * @package Slub\FindSlub\ViewHelpers\Find
 */
class HoldingStatusJsonViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Registers own arguments.
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('data', 'array|string|int|float', 'The holding data', FALSE, NULL);
	}

	/**
	 * @return string
	 */
	public function render() {

		$status = 9999;
		$data = $this->arguments['data'];

		if($data['documents'][0]['access_facet'] == "Local Holdings") {

			foreach($data['enriched']['fields']['exemplare'] as $exemplar) {

				if($exemplar['_calc_colorcode'] < $status) {
					$status = $exemplar['_calc_colorcode'];
				}

			}
		} elseif($data['documents'][0]['access_facet'] =="Electronic Resources") {
			$status = 1;
		}

		return json_encode(array('status' => $status));

	}

}

?>