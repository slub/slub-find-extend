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
 * IsArrayEmptyViewHelper
 *
 * Checks if an array is empty.
 *
 */
class IsArrayEmptyViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('array', 'array', 'The array to check', TRUE, NULL);
    }

    /**
     * @return bool the rendered string
     */
    public function render() {
        $array = $this->arguments['array'];
		return empty($array);
    }
}
