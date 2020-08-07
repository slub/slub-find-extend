<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ReduceArrayViewHelper extends AbstractViewHelper  {

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('array', 'array', 'array to reduce', FALSE);
        $this->registerArgument('remaining', 'string', 'remaining string to decode', FALSE);
    }

    /**
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        $array = $arguments['array'];
        $remaining = $arguments['remaining'];

        $result = [];

        if ($array === NULL) {
            $array = $renderChildrenClosure();
        }

        if(count($array) === 0) {
            return [];
        }

        $remaining = json_decode($remaining, TRUE);
        if(count($remaining) === 0) {
            return [];
        }

        foreach ($array as $part) {

            $newPart = [];

            foreach ($remaining as $key => $value) {

                $valueKeys = array_map('trim', explode(',', $value));

                if(count($valueKeys) === 1) {
                    $newPart[$key] = $part[$valueKeys[0]];
                } elseif (count($valueKeys) > 0) {
                    foreach ($valueKeys as $valueKey) {
                        if(!is_array($newPart[$key])) {
                            $newPart[$key] = [];
                        }

                        if(array_key_exists($valueKey, $part)) {
                            if (!is_array($part[$valueKey])) {
                                $part[$valueKey] = [$part[$valueKey]];
                            }
                            $newPart[$key] = array_merge($newPart[$key], $part[$valueKey]);
                        }
                    }
                } else {
                    $newPart[$key] = '';
                }

            }

            $result[] = $newPart;

        }

        return $result;
    }

}
