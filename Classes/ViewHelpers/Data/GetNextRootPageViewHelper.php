<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**

 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * GetRootPageViewHelper
 */
class GetNextRootPageViewHelper extends AbstractViewHelper
{
    /**
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        // would be nice to use the RootlineUtility at this point, but the current page uid is mandatory but not available at this point IMHO.
        $rootline = $GLOBALS['TSFE']->rootLine;
        array_reverse($rootline);

        foreach ($rootline as $page) {
            if ($page['is_siteroot'] === 1) {
                return $page;
            }
        }

        return [];
    }
}
