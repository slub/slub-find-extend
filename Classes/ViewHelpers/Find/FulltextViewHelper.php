<?php

namespace Slub\SlubFindExtend\ViewHelpers\Find;


use Slub\SlubFindExtend\Services\FulltextService;
use Solarium\QueryType\Select\Result\Document;

class FulltextViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \Slub\SlubFindExtend\Services\FulltextService
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