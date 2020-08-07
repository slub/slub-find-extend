<?php
namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 * FilterBlacklistedViewHelper
 *
 * Filters the values of an array against a blacklist.
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class FilterBlacklistedViewHelper extends AbstractViewHelper {
	/**
	 * Register arguments.
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('data', 'array', 'The data checked against the blacklist', TRUE, NULL);
		$this->registerArgument('blacklist', 'array', 'The blacklist to be parsed', TRUE, NULL);
		$this->registerArgument('blacklistOnKeys', 'boolean', 'Blacklist on array values (default: false) or array keys (true)', FALSE, FALSE);
	}

    /**
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
		if (is_array($arguments['data']) && is_array($arguments['blacklist'])) {
			if( $arguments['blacklistOnKeys'] === FALSE ) {
				return preg_grep('/^(' . str_replace('/', '\/', implode('|', $arguments['blacklist'])) . ')$/', $arguments['data'], PREG_GREP_INVERT);
			} else {
				return array_flip(preg_grep('/^(' . str_replace('/', '\/', implode('|', $arguments['blacklist'])) . ')$/', array_flip($arguments['data']), PREG_GREP_INVERT));
			}
		} elseif (is_array($arguments['data'])) {
			return $arguments['data'];
		} else {
			return array();
		}
	}
}