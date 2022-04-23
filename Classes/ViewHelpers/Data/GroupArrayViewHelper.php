<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 *
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GroupArrayViewHelper extends AbstractViewHelper
{
    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('array', 'array', 'The first array.', true, array());
        $this->registerArgument('groupby', 'string', 'One or more Array elements to create a group by', true, '');
    }

    /**
     * @return array
     */
    public function render()
    {
        $array = $this->arguments['array'];
        $groupby = explode(',', $this->arguments['groupby']);

        $groupedArray = [];

        foreach ($array as $elementObj) {

            // Ensure arary. Might be object
            $element = (array)$elementObj;

            $isGrouped = false;

            foreach ($groupedArray as $key => $group) {
                $inGroup = false;
                foreach ($groupby as $by) {
                    if ($group[$by] === $element[$by]) {
                        $inGroup = true;
                    } else {
                        $inGroup = false;
                    }
                }

                if ($inGroup) {
                    $isGrouped = true;
                    $groupedArray[$key]['objects'][] = $element;
                }
            }

            if (!$isGrouped) {
                $newGroup = [];
                $newGroup['objects'][] = $element;
                foreach ($groupby as $by) {
                    $newGroup[$by] = $element[$by];
                }

                $groupedArray[] = $newGroup;
            }
        }

        $this->templateVariableContainer->add('groupedarray', $groupedArray);

        return $this->renderChildren();
    }
}
