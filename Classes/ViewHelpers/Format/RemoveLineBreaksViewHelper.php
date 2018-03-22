<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

class RemoveLineBreaksViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper  {

	/**
	 * Removes line breaks inside content
	 *
	 * @param string $content
	 * @return string
	 */
	public function render($content = NULL) {
		if ($content === NULL) {
			$content = $this->renderChildren();
		}

		if ($content && (strlen($content) > 0)){
			return str_replace(array("\r\n", "\n", "\r"), ' ', $content);
		} else {
			return '';
		}
	}

}
