<?php
namespace Slub\SlubFindExtend\ViewHelpers\Data;

	/***************************************************************
	 *
	 *  Copyright notice
	 *
	 *  This script is part of the TYPO3 project. The TYPO3 project is
	 *  free software; you can redistribute it and/or modify
	 *  it under the terms of the GNU General Public License as published by
	 *  the Free Software Foundation; either version 3 of the License, or
	 *  (at your option) any later version.
	 *
	 *  The GNU General Public License can be found at
	 *  http://www.gnu.org/copyleft/gpl.html.
	 *
	 *  This script is distributed in the hope that it will be useful,
	 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *  GNU General Public License for more details.
	 *
	 *  This copyright notice MUST APPEAR in all copies of the script!
	 ***************************************************************/

/**
 * FilterBlacklistedViewHelper
 *
 * Filters the values of an array against a blacklist.
 *
 */
class FilterBlacklistedViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
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

	public function render() {
		if (is_array($this->arguments['data']) && is_array($this->arguments['blacklist'])) {
			if( $this->arguments['blacklistOnKeys'] === FALSE ) {
				return preg_grep('/^(' . implode('|', $this->arguments['blacklist']) . ')$/', $this->arguments['data'], PREG_GREP_INVERT);
			} else {
				return array_flip(preg_grep('/^(' . implode('|', $this->arguments['blacklist']) . ')$/', array_flip($this->arguments['data']), PREG_GREP_INVERT));
			}
		} elseif (is_array($this->arguments['data'])) {
			return $this->arguments['data'];
		} else {
			return array();
		}
	}
}