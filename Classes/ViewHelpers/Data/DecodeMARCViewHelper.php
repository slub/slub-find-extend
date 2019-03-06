<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**

 */
class DecodeMARCViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Register arguments.
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('raw', 'string', 'The raw MARC to decode', FALSE, NULL);
		$this->registerArgument('field', 'string', 'Return data as array field??', FALSE, NULL);
	}

	/**
	 * @return array
	 */
	public function render() {

        $raw = $this->arguments['raw'];
        $field = $this->arguments['field'];

        if ($raw === NULL) {
            $raw = $this->renderChildren();
        }

        $decoder = new \Slub\SlubFindExtend\Slots\Decoder\Marc21();
        $decoded = $decoder->decode($raw);

        if ($field !== NULL) {
            $return = [];
            $return[$field] = $decoded;
        } else {
            $return = $decoded;
        }

        return $return;
	    
	}

}

?>
