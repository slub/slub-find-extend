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

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GroupDocumentsViewHelper extends AbstractViewHelper {

    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Registers arguments.
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('documents', 'array', '', true);
        $this->registerArgument('groupby', 'string', 'field to group documents by', true);
        $this->registerArgument('sortby', 'string', 'field to sort group by', false, null);
    }

    /**
     *
     * @return array
     */
    public function render() {
        $documents = $this->arguments['documents'];
        $groupby = $this->arguments['groupby'];
        $sortby = $this->arguments['sortby'];
        $groups = [];
        $defaultValue = 'no_group';

        if (is_countable($documents)) {
            foreach ($documents as $document) {
                if (isset($document[$groupby])) {
                    if (array_key_exists($document[$groupby], $groups)) {
                        $groups[$document[$groupby]]['documents'][] = $document;
                    } else {
                        $groups[$document[$groupby]]['documents'] = [$document];
                    }
                } else {
                    if (array_key_exists($defaultValue, $groups)) {
                        $groups[$defaultValue]['documents'][] = $document;
                    } else {
                        $groups[$defaultValue]['documents'] = [$document];
                    }
                }
            }

            if (!empty($sortby)) {
                foreach ($groups as &$group) {
                    usort($group['documents'], $this->sortArrays($sortby));
                }
            }
        }

        $this->templateVariableContainer->add('groupeddocuments', $groups);

        return $this->renderChildren();
    }

    private function sortArrays($key) {
        return function ($a, $b) use ($key) {
            if ($a[$key] == $b[$key]) {
                return 0;
            }
            return ($a[$key] < $b[$key]) ? -1 : 1;
        };
    }
}

?>
