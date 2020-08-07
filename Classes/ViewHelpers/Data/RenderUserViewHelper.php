<?php
namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 *
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class RenderUserViewHelper extends AbstractViewHelper {

    public function render (){

        $this->templateVariableContainer->add('user', $GLOBALS['TSFE']->fe_user->user);
        return $this->renderChildren();
    }

}