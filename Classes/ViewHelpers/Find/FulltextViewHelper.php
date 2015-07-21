<?php

namespace Slub\FindSlub\ViewHelpers\Find;


use Slub\FindSlub\Services\FulltextService;
use Solarium\QueryType\Select\Result\Document;

class FulltextViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \Slub\FindSlub\Services\FulltextService
	 * @inject
	 */
	protected $fulltextService;

	/**
	 * Registers own arguments.
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('document', '\Solarium\QueryType\Select\Result\Document', 'Result document', TRUE);
	}

	/**
	 * @return string
	 */
	public function render() {

		return $this->fulltextService->getFulltextLink($this->arguments['document']);

	}



}

?>