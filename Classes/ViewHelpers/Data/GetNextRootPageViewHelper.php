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
 * GetRootPageViewHelper
 */
class GetNextRootPageViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    public function render (){

        $rootline = $GLOBALS['TSFE']->rootLine;
        array_reverse($rootline);

        foreach ($rootline as $page) {
            if($page['is_siteroot'] === '1') {
                return $page;
            }
        }

        return [];

    }

}