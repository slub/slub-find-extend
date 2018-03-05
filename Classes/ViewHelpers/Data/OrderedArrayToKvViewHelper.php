<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;


class OrderedArrayToKvViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper  {

    /**
     * COmbines ordered Keys and values to kv
     *
     * @param array $array
     * @param string $remaining
     * @return array
     */
    public function render($array = NULL) {

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

			foreach ($value as $innervalue) {

				if ($isKey === TRUE) {
					$innerkey = $innervalue;
					$isKey = FALSE;
				} elseif ($isKey === FALSE) {
					$innerresult[$innerkey] = $innervalue;
					$isKey = TRUE;
				}

			}

			$result[$key] = $innerresult;

		}

		return $result;
    }

}
