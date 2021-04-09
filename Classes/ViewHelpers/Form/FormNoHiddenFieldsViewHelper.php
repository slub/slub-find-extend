<?php

namespace Slub\SlubFindExtend\ViewHelpers\Form;

class FormNoHiddenFieldsViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper {
    protected function renderHiddenReferrerFields(){
        return '';
    }
    protected function renderTrustedPropertiesField(){
        return '';
    }
}