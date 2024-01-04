<?php

namespace Slub\SlubFindExtend\ViewHelpers\Link;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class LinksFromDataViewHelper extends AbstractViewHelper
{


    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('marc', 'string', 'The raw MARC', false, null);
        $this->registerArgument('document', 'array', 'The Solr doc', false, null);

    }

    /**
     * Render the link with prefix
     * 
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        return array();
    }

}