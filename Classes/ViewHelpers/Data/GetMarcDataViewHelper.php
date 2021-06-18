<?php
namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 *
 */

use File_MARC_Reference;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('slub_find_extend') . 'vendor/autoload.php');

/**
 * GetMarcDataViewHelper
 */
class GetMarcDataViewHelper extends AbstractViewHelper
{

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('record', 'mixed', 'The decoded MARC record', FALSE, NULL);
        $this->registerArgument('path', 'string', 'The MARC path', FALSE, NULL);
        $this->registerArgument('index', 'integer', 'If return data might be an array, define which index should be returned', FALSE, NULL);
    }

    /**
     * @return mixed
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        if($arguments['record']) {

            $reference = new File_MARC_Reference((string)$arguments['path'], $arguments['record']);

            if($arguments['index'] !== NULL && is_array($reference->content)) {
                return $reference->content[$arguments['index']];
            } else {
                return $reference->content;
            }

        }

        return NULL;

    }

}
