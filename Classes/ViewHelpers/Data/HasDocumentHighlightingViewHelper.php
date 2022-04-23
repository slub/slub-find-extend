<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 * HasDocumentHighlightingViewHelper
 *
 * Checks if this document has highlighting
 *
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

class HasDocumentHighlightingViewHelper extends AbstractConditionViewHelper
{
    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('highlighting', 'array', 'Result array with highlighting per document', true);
        $this->registerArgument('id', 'string', 'document id to check', true);
    }

    /**
     */
    public static function verdict(array $arguments, \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext)
    {
        $templateVariableContainer = $renderingContext->getVariableProvider();

        $highlights = [];

        $resultFields = explode(',', $templateVariableContainer->get('settings')['highlightingCheckFields']);
        $resultIgnoreFields = explode(',', $templateVariableContainer->get('settings')['highlightingCheckFieldsIgnore']);
        $resultExclusiveFields = explode(',', $templateVariableContainer->get('settings')['highlightingCheckFieldsExclusive']);
        $exclusiveHit = false;


        if ($arguments['highlighting'][$arguments['id']]->getFields()) {
            foreach ($arguments['highlighting'][$arguments['id']]->getFields() as $key => $hit) {
                if ($exclusiveHit) {
                    break;
                }

                if (!in_array($key, $resultIgnoreFields)) {
                    $highlights[] = [$key, $hit];
                }

                if (in_array($key, $resultExclusiveFields)) {
                    $highlights = [[$key, $hit]];
                    $exclusiveHit = true;
                }

                if (in_array($key, $resultFields)) {
                    return true;
                }
            }
        }

        $templateVariableContainer->add('highlights', $highlights);
        return false;
    }
}
