<?php

namespace Slub\SlubFindExtend\ViewHelpers\Find;

class MetaTagViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper  {

    public function initializeArguments()
    {
        $this->registerArgument('property', 'string', 'meta tag property');
        $this->registerArgument('content', 'string', 'meta tag content');
    }

     /**
     * Renders the tag
     */
    public function render()
    {

        if (empty($this->arguments['property'])) {
          return;
        }

        if (!empty($this->arguments['content'])) {
            $content = $this->arguments['content'];
        } else {
            $content = $this->renderChildren();
        }

        $metaTag = '<meta property="'.$this->arguments['property'].'" content="' . $content . '">';

        $GLOBALS['TSFE']->additionalHeaderData[$this->arguments['property']] = $metaTag;

        return;
    }
}