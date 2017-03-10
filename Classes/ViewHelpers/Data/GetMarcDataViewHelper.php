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

use File_MARC_Reference;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('slub_find_extend') . 'vendor/autoload.php');

/**
 * GetMarcDataViewHelper
 */
class GetMarcDataViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('record', '\File_MARC_Record|boolean', 'The decoded MARC record', FALSE, NULL);
        $this->registerArgument('path', 'string', 'The MARC path', FALSE, NULL);
        $this->registerArgument('index', 'integer', 'If return data might be an array, define which index should be returned', FALSE, NULL);
    }

    public function render (){


        if($this->arguments['record']) {

            $reference = new File_MARC_Reference($this->arguments['path'], $this->arguments['record']);

            if($this->arguments['index'] !== NULL && is_array($reference->content)) {
                return $reference->content[$this->arguments['index']];
            } else {
                return $reference->content;
            }

        }

        return NULL;

    }

}