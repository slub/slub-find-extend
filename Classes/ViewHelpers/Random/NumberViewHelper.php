<?php

namespace Slub\SlubFindExtend\ViewHelpers\Random;

/*
 * This file is part of the FluidTYPO3/Vhs project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * ### Random: Number Generator
 *
 * Generates a random number. The default minimum number is
 * set to 100000 in order to generate a longer integer string
 * representation. Decimal values can be generated as well.
 */
class NumberViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument(
            'minimum',
            'integer',
            'Minimum number - defaults to 100000 (default max is 999999 for equal string lengths)',
            false,
            100000
        );
        $this->registerArgument(
            'maximum',
            'integer',
            'Maximum number - defaults to 999999 (default min is 100000 for equal string lengths)',
            false,
            999999
        );
        $this->registerArgument(
            'minimumDecimals',
            'integer',
            'Minimum number of also randomized decimal digits to add to number',
            false,
            0
        );
        $this->registerArgument(
            'maximumDecimals',
            'integer',
            'Maximum number of also randomized decimal digits to add to number',
            false,
            0
        );
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return integer|float
     */
    public function render()
    {
        $minimum = $this->arguments['minimum'];
        $maximum = $this->arguments['maximum'];
        $minimumDecimals = $this->arguments['minimumDecimals'];
        $maximumDecimals = $this->arguments['maximumDecimals'];
        $natural = random_int($minimum, $maximum);
        if (0 === (int)$minimumDecimals && 0 === (int)$maximumDecimals) {
            return $natural;
        }
        $decimals = array_fill(0, random_int($minimumDecimals, $maximumDecimals), 0);
        $decimals = array_map(function () {
            return random_int(0, 9);
        }, $decimals);
        return $natural . '.' . implode('', $decimals);
    }
}
