<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class NotAssignedViewHelper extends AbstractViewHelper


	private static $notAssignedStrings = ['No subject assigned', 'not assigned', 'Not assigned'];

	/**
	 * Register arguments.
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('data', 'mixed', 'The data to test', FALSE, NULL);
	}

    /**
     * @return boolean
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

		$data = $arguments['data'];

		if(in_array($data, static::$notAssignedStrings)) {
			return false;
		}

		if(is_array($data)) {
			if(in_array($data[0], static::$notAssignedStrings)) {
				return false;
			}
		}

		return true;

	}

}

?>
