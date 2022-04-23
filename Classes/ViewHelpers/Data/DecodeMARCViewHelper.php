<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**

 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class DecodeMARCViewHelper extends AbstractViewHelper
{
    /**
     * Register arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('raw', 'string', 'The raw MARC to decode', false, null);
        $this->registerArgument('field', 'string', 'Return data as array field??', false, null);
    }

    /**
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $raw = $arguments['raw'];
        $field = $arguments['field'];

        if ($raw === null) {
            $raw = $renderChildrenClosure();
        }

        $decoder = new \Slub\SlubFindExtend\Slots\Decoder\Marc21();
        $decoded = $decoder->decode($raw);

        if ($field !== null) {
            $return = [];
            $return[$field] = $decoded;
        } else {
            $return = $decoded;
        }

        return $return;
    }
}
