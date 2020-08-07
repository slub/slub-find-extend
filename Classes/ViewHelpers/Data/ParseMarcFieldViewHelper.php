<?php
namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 * ParseMarcFieldViewHelper
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ParseMarcFieldViewHelper extends AbstractViewHelper {

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('field', 'string', 'The marc field string', FALSE, NULL);
        $this->registerArgument('subfieldasarray', 'boolean', 'Return subfields as array?', FALSE, FALSE);
        $this->registerArgument('orderedarray', 'boolean', 'Return subfields as array ordered as in original data?', FALSE, FALSE);
        $this->registerArgument('getindicators', 'boolean', 'Return indicator1 and indicator2 as fields', FALSE, FALSE);
    }

    /**
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        $output = [];

        if(is_array($arguments['field'])) {

            foreach($arguments['field'] as $field) {
                $fieldData = explode('',$field);

                if(strlen(trim($fieldData[0])) > 0) {

                    $index = intval(substr($fieldData[0], 0, 1));

                    if(!is_array($output[$index])) {
                        $output[$index] = [];
                    }

                    if($arguments['subfieldasarray'] === TRUE) {
                        $dataCleaned = static::cleanedArrayData($fieldData, TRUE, $arguments['orderedarray']);
                    } else {
                        $dataCleaned = static::cleanedArrayData(array_slice($fieldData,1), FALSE, $arguments['orderedarray']);
                    }

                    if($arguments['getindicators'] === TRUE) {

                        $dataCleaned['ind1'] = substr($fieldData[0], 0, 1);
                        $dataCleaned['ind2'] = substr($fieldData[0], 1, 1);

                    }

                    $output[$index][] = $dataCleaned;


                } else {
                    $output[] = static::cleanedArrayData($fieldData, TRUE);
                }

            }

        }

        return $output;

    }

    /**
     * @param $arr
     * @param $subfieldasarray
     * @param $orderedarray
     * @return array
     */
    private static function cleanedArrayData($arr, $subfieldasarray = FALSE, $orderedarray = FALSE) {

        $return = [];
        $ordered = [];

        foreach($arr as $fieldData) {

            if(substr($fieldData, 2, 1) === ':') {
                $offset = substr($fieldData, 0, 2);
                $data = trim(substr($fieldData, 3),'');
            } else {
                $offset = substr($fieldData, 0, 1);
                $data = trim(substr($fieldData, 1),'');
            }

            $ordered[] = ['subfield' => $offset, 'data' => $data];

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

        if($orderedarray) $return['_ordered'] = $ordered;

        return $return;
    }

}
