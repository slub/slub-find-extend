<?php

namespace Slub\SlubFindExtend\ViewHelpers\Find;

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

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;


/**
 * UpdateSortConfigViewHelper
 *
 * updates sorting label if there is an alternative label
 */
class UpdateSortConfigViewHelper extends AbstractViewHelper {


    /**
     * Registers own arguments.
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('translate', 'array', 'translate path and extension', false, false);
    }


    /**
     *
     * @return string
     */
     public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $templateVariableContainer = $renderingContext->getVariableProvider();

        if ($templateVariableContainer->exists('config')) {
            if (is_array($templateVariableContainer->get('settings')['sort'])) {
                foreach ($templateVariableContainer->get('settings')['sort'] as $sort) {
                    if ($sort['label'] && $sort['sortCriteria']) {
                        $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang-form.xml:input.sort-' . $sort['label'];
                        $localisedLabel = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey, $arguments['translate']) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey, $arguments['translate']) : $sort['label'];
                        $config = $templateVariableContainer->get('config');
                        $config['sortOptions']['menu'][$sort['sortCriteria']] = $localisedLabel;

                        $templateVariableContainer->remove('config');
                        $templateVariableContainer->add('config', $config);
                    }
                }
            }
        }

        return $renderChildrenClosure();
    }
}

?>
