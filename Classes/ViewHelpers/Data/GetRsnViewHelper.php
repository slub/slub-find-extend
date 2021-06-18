<?php
namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 * FilterBlacklistedViewHelper
 *
 * Filters the values of an array against a blacklist.
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * GetRsnViewHelper
 */
class GetRsnViewHelper extends AbstractViewHelper
{

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('rsns', 'array', 'The RSNs to search', FALSE, NULL);
        $this->registerArgument('isil', 'string', 'ISIL to match', FALSE, NULL);
    }

    /**
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        if($arguments['rsns']) {
            foreach ($arguments['rsns'] as $rsn) {
                if (preg_match('/^.*'.$arguments['isil'].'.?(.*?)$/', $rsn, $matches) === 1) {
                    return $matches[1];
                }
            }
        }

        return '';

    }

}
