<?php

namespace Slub\SlubFindExtend\ViewHelpers\Logic;

/*
 * This file is part of the FluidTYPO3/Vhs project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * ### Condition: String contains substring
 *
 * Condition ViewHelper which renders the `then` child if provided
 * string $haystack contains provided string $needle.
 *
 * @author Björn Fromme <fromme@dreipunktnull.com>, dreipunktnull
 * @package Vhs
 * @subpackage ViewHelpers\Condition\String
 */
class ContainsViewHelper extends AbstractConditionViewHelper {

    /**
     * Registers own arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('haystacks', 'string|array', 'Haystack to search', TRUE);
        $this->registerArgument('needle', 'string', 'Needle to be searched', TRUE);
    }

    public static function verdict(array $arguments, \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {

        $templateVariableContainer = $renderingContext->getVariableProvider();

        $haystacks = $arguments['haystacks'];
        $needle = $arguments['needle'];

        if(!is_array($haystacks)) {
            $haystacks = [$haystacks];
        }

        foreach ($haystacks as $haystack) {
            if (FALSE !== strpos($haystack, $needle)) {

                if ($templateVariableContainer->exists('hit')) {
                    $templateVariableContainer->remove('hit');
                }
                $templateVariableContainer->add('hit', $haystack);

                return TRUE;
            }
        }

        return FALSE;
        
    }

}
