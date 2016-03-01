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
	}

	public function render() {
		if( is_array($this->arguments['data']) && is_array($this->arguments['blacklist'])) {
			return array_diff($this->arguments['data'], $this->arguments['blacklist']);
		} else {
			return array();
		}
	}
}