<?php
namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**

 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * GetRootPageViewHelper
 */
class GetNextRootPageViewHelper extends AbstractViewHelper {

    /**
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

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