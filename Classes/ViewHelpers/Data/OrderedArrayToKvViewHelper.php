<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class OrderedArrayToKvViewHelper extends AbstractViewHelper  {

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('array', 'array', 'Array with keys and values', FALSE, NULL);
        $this->registerArgument('translate', 'boolean', 'Should we try to translate data?', FALSE, FALSE);
        $this->registerArgument('translatekey', 'string', 'Where to find translation?', FALSE);
        $this->registerArgument('translatekeyextension', 'string', 'Where to find translation?', FALSE);
        $this->registerArgument('keeporiginalvalue', 'boolean', 'Should we keep original value?', FALSE);
    }

    /**
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

		$result = [];

		$array = $arguments['array'];
        $translate = $arguments['translate'];
        $translatekey = $arguments['translatekey'];
        $translatekeyextension = $arguments['translatekeyextension'];
        $keeporiginalvalue = $arguments['keeporiginalvalue'];

		if ($array === NULL) {
			$array = $renderChildrenClosure;
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
