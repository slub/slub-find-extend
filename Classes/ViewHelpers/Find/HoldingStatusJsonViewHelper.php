<?php

namespace Slub\SlubFindExtend\ViewHelpers\Find;

/**
 * Class HoldingStatusJsonViewHelper
 * @package Slub\SlubFindExtend\ViewHelpers\Find
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
	 * Returns the holding state
	 *
	 * @param $exemplare
	 * @return int
	 */
	private function getStatusFromArray($exemplare) {

		$status = 9999;

		foreach ($exemplare as $exemplar) {

			if($exemplar['elements'] && is_array($exemplar['elements'])) {
				$status = $this->getStatusFromArray($exemplar['elements']);
			} elseif ( ( $exemplar['_calc_colorcode'] < $status ) && ( $status != 1) ) {
				$status = $exemplar['_calc_colorcode'];
			}

		}

		return $status;

	}

	/**
	 * @return string
	 */
	public function render() {

		$data = $this->arguments['data'];

		if($data['documents'][0]['access_facet'] == "Local Holdings") {

			if($data['enriched']['fields']['exemplare']) {

				$status = $this->getStatusFromArray($data['enriched']['fields']['exemplare']);

			} else {

				// Somehow this is a Local Holdings file with no copies. Send "Action needed" state.
				return json_encode(array('status' => 2));
			}

		} elseif($data['documents'][0]['access_facet'] =="Electronic Resources") {
			$status = 1;
		}

		return json_encode(array('status' => $status));

	}

}

?>