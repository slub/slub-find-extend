<?php

namespace Slub\SlubFindExtend\ViewHelpers\Logic;

/*
 * This file is part of the FluidTYPO3/Vhs project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

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

    /**
     * Render method
     *
     * @param string|array $haystacks
     * @param string $needle
     * @return string
     */
    public function render() {

        $haystacks = $this->arguments['haystacks'];
        $needle = $this->arguments['needle'];

        if(!is_array($haystacks)) {
            $haystacks = [$haystacks];
        }

        foreach ($haystacks as $haystack) {
            if (FALSE !== strpos($haystack, $needle)) {

                if ($this->templateVariableContainer->exists('hit')) {
                    $this->templateVariableContainer->remove('hit');
                }
                $this->templateVariableContainer->add('hit', $haystack);

                return $this->renderThenChild();
            }
        }

        return $this->renderElseChild();
        
    }

}
