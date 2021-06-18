<?php
namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 * PrefixedValueFromArrayViewHelper
 *
 * Gets a vlaue from an array when it is prefixed
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class PrefixedValueFromArrayViewHelper extends AbstractViewHelper
{

	/**
	 * Register arguments.
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('prefix', 'string', 'prefix', TRUE);
		$this->registerArgument('prefixSprint', 'string', 'prefix sprintf string', TRUE);
		$this->registerArgument('array', 'array', 'values', TRUE);
	}

    /**
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

		if (!is_array($arguments['array'])) {
			return '';
		}

		$prefix = sprintf($arguments['prefixSprint'], $arguments['prefix']);

		foreach ($arguments['array'] as $data) {

			if(substr($data,0,strlen($prefix)) === $prefix) {
				return substr($data,strlen($prefix));
			}
		}

		return '';

	}

}
