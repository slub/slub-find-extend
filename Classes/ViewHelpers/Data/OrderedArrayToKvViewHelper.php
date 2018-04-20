<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;


class OrderedArrayToKvViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper  {

    /**
     * Combines ordered Keys and values to kv. Possible to translate.
     *
     * @param array $array
     * @param boolean $translate
     * @param string $translatekey
     * @param string $translatekeyextension
     * @param boolean $keeporiginalvalue
     * @return array
     */
    public function render($array = NULL, $translate = FALSE, $translatekey = '', $translatekeyextension = '', $keeporiginalvalue = FALSE) {

		$result = [];

		if ($array === NULL) {
			$array = $this->renderChildren();
		}

		if(count($array) === 0) {
			return [];
		}

		foreach ($array as $key => $value) {

			$innerresult = [];
			$innerkey = '';
			$isKey = TRUE;

			$keyValue = ($translate) ?  \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($translatekey.$key, $translatekeyextension) : $key;
			if(strlen($keyValue) === 0) $keyValue = $key;

			foreach ($value as $innervalue) {

				if ($isKey === TRUE) {
					$innerkey = $innervalue;
					$isKey = FALSE;
				} elseif ($isKey === FALSE) {

					$innerkeyValue = ($translate) ?  \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($translatekey.$key.'.'.$innerkey, $translatekeyextension) : $innerkey;
					if(strlen($innerkeyValue) === 0) $innerkeyValue = $innerkey;

					if($keeporiginalvalue) {
						$innerresult[$innerkey] = [];
						$innerresult[$innerkey]['translation'] = $innerkeyValue;
						$innerresult[$innerkey]['values'] = $innervalue;
					} else {
						$innerresult[$innerkeyValue] = $innervalue;
					}
					$isKey = TRUE;
				}

			}

			if($keeporiginalvalue) {
				$result[$key] = [];
				$result[$key]['translation'] = $keyValue;
				$result[$key]['values'] = $innerresult;
			} else {
				$result[$keyValue] = $innerresult;
			}

		}

		return $result;
    }

}
