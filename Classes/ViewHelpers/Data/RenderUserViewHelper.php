<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 *
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class RenderUserViewHelper extends AbstractViewHelper
{
    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    public function render (){

        $this->templateVariableContainer->add('user', $GLOBALS['TSFE']->fe_user->user);

        $user = $GLOBALS['TSFE']->fe_user->user;
        $userid = $user['username'];
        if (strpos($user['telephone'], "@") !== false) {
            $userid = explode('@',$user['telephone'])[0];
        }

        $this->templateVariableContainer->add('userid', $userid);

        return $this->renderChildren();
    }
}
