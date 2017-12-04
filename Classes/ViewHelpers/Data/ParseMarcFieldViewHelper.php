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
 * ParseMarcFieldViewHelper
 */
class ParseMarcFieldViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('field', 'string', 'The marc field string', FALSE, NULL);
        $this->registerArgument('subfieldasarray', 'boolean', 'Return subfields as array?', FALSE, NULL);
    }

    /**
     * @return array
     */
    public function render (){

        $output = [];

        if(is_array($this->arguments['field'])) {

            foreach($this->arguments['field'] as $field) {
                $fieldData = explode('',$field);

                if(strlen(trim($fieldData[0])) > 0) {

                    $index = intval(substr($fieldData[0], 0, 1));

                    if(!is_array($output[$index])) {
                        $output[$index] = [];
                    }

                    if($this->arguments['subfieldasarray'] === TRUE) {
                        $output[$index][] = $this->cleanedArrayData($fieldData, TRUE);
                    } else {
                        $output[$index][] = $this->cleanedArrayData(array_slice($fieldData,1));
                    }


                } else {
                    $output[] = $this->cleanedArrayData($fieldData,1);
                }

            }

        }

        return $output;

    }

    /**
     * @param $arr
     * @return array
     */
    private function cleanedArrayData($arr, $subfieldasarray = FALSE) {

        $return = [];

        foreach($arr as $fieldData) {

            $offset = substr($fieldData, 0, 1);
            $data = trim(substr($fieldData, 1),'');
            if($subfieldasarray === TRUE) {
                if(is_array($return[$offset])) {
                    $return[$offset][] = $data;
                } else {
                    $return[$offset] = [$data];
                }
            } else {
                $return[$offset] = $data;
            }

        }

        return $return;
    }

}