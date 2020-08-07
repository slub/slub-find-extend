<?php
namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 * HasDocumentHighlightingViewHelper
 *
 * Checks if this document has highlighting
 *
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

class HasDocumentHighlightingViewHelper extends AbstractConditionViewHelper {

	/**
	 * Register arguments.
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('highlighting', 'array', 'Result array with highlighting per document', TRUE);
		$this->registerArgument('id', 'string', 'document id to check', TRUE);
	}

	/**
	 */
	public function render() {

		$highlights = [];

		$resultFields = explode(',',$this->templateVariableContainer->get('settings')['highlightingCheckFields']);
		$resultIgnoreFields = explode(',',$this->templateVariableContainer->get('settings')['highlightingCheckFieldsIgnore']);
		$resultExclusiveFields = explode(',',$this->templateVariableContainer->get('settings')['highlightingCheckFieldsExclusive']);
		$exclusiveHit = false;


		if($this->arguments['highlighting'][$this->arguments['id']]->getFields()) {
			foreach($this->arguments['highlighting'][$this->arguments['id']]->getFields() as $key => $hit) {
				if($exclusiveHit) { break; }

				if(!in_array($key, $resultIgnoreFields)) {
					$highlights[] = [$key, $hit];
				}

				if(in_array($key, $resultExclusiveFields)) {
					$highlights = [[$key, $hit]];
					$exclusiveHit = true;
				}

				if(in_array($key, $resultFields)) {
					return $this->renderThenChild();
				}
			}
		}

		$this->templateVariableContainer->add('highlights', $highlights);
		return $this->renderElseChild();

	}

}