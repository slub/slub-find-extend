<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;


/**
 * Test whether needle is in an haystack array
 *
 */
class InArrayViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {


    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('haystack', 'array', 'Array to search', TRUE);
        $this->registerArgument('needle', 'string', 'Needle to search', TRUE);
        $this->registerArgument('strict', 'boolean', 'Strict mode?', FALSE, FALSE);
    }

    public function render() {

        if(!is_array($this->arguments['haystack'])) {
            return FALSE;
        }

        return in_array ( $this->arguments['needle'] , $this->arguments['haystack'], $this->arguments['strict'] );

    }

}
