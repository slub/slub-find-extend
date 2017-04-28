<?php

namespace Slub\SlubFindExtend\ViewHelpers\Find;


use Slub\SlubFindExtend\Services\HoldingStatusService;

class HoldingStatusViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \Slub\SlubFindExtend\Services\HoldingStatusService
	 * @inject
	 */
	protected $holdingStatusService;

    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('document', 'object', 'The index document', TRUE);
        $this->registerArgument('copies', 'array', 'The the holded copies', FALSE, []);
    }


	/**
	 * @return string
	 */
	public function render() {
		return $this->holdingStatusService->getStatus($this->arguments['document'], $this->arguments['copies']);
	}


}

?>