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

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('slub_find_extend') . 'vendor/autoload.php');

/**
 * GetAllMarcDataViewHelper
 */
class GetAllMarcDataViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('record', 'mixed', 'The decoded MARC record', FALSE, NULL);
    }

    public function render (){

        $lines = [];

        /** @var \File_MARC_Record $record */
        $record = $this->arguments['record'];

        if($record) {

            $leader = $record->getLeader();
            if($leader) {
                $lines[] = $leader;
            }

            foreach ($record->getFields() as $tag=>$value) {

                $line = "";
                $line .= "$tag ";

                if ($value instanceof \File_MARC_Control_Field) {
                    $line .= $value->getData();
                }

                else {

                    // Iterate through the subfields in this data field
                    $line .= $value->getIndicator(1);
                    $line .= $value->getIndicator(2);

                    foreach ($value->getSubfields() as $code=>$subdata) {
                        $line .= "\$$code";

                        /** \File_MARC_Subfield $subdata */
                        $line .= $subdata->getData();
                    }

                }



                $lines[] = $line;

            }

            $this->templateVariableContainer->add('lines', $lines);

        }

        return $this->renderChildren();

    }

}