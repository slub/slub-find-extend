<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;


class ReduceArrayViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper  {

    /**
     * urldecodes content
     *
     * @param array $array
     * @param string $remaining
     * @return array
     */
    public function render($array = NULL, $remaining = '') {

        $result = [];

        if ($array === NULL) {
            $array = $this->renderChildren();
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
