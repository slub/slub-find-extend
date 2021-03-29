<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**

 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ArrayAccessViewHelper extends AbstractViewHelper {

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
     * @return string
     */
	public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
		$data = $arguments['data'];
		return $data[$arguments['index']];
	}

}

?>