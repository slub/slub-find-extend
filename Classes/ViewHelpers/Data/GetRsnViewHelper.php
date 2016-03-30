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
 * GetRsnViewHelper
 */
class GetRsnViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('rsns', 'array', 'The RSNs to search', FALSE, NULL);
        $this->registerArgument('isil', 'string', 'ISIL to match', FALSE, NULL);
    }

    public function render (){

        if($this->arguments['rsns']) {
            foreach ($this->arguments['rsns'] as $rsn) {
                if (preg_match('/^.*'.$this->arguments['isil'].'.?(.*?)$/', $rsn, $matches) === 1) {
                    return $matches[1];
                }
            }
        }

        return '';

    }

}