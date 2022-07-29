<?php

namespace Slub\SlubFindExtend\ViewHelpers\Find;

/**
 *
 */

use Slub\SlubFindExtend\Services\HoldingStatusService;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class HoldingLocationViewHelper extends AbstractViewHelper
{
    /**
     * @var \Slub\SlubFindExtend\Services\HoldingStatusService
     */
    protected static $holdingStatusService;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('document', 'object', 'The index document', true);
        $this->registerArgument('copies', 'array', 'The the holded copies', false, []);
    }

    /**
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        if ($arguments['document']) {
            $document = $arguments['document'];

            $status = static::getHoldingStatusService()->getStatus($document, $arguments['copies']);

            if (($status === 1) && (in_array('Local', $document['facet_avail']))) {
                foreach ($arguments['copies'] as $exemplar) {
                    if ($exemplar['_calc_colorcode'] == 1) {
                        return $exemplar['Regalstandort'];
                    }
                }
            }
            return '';
        } else {
            return '';
        }
    }

    private static function getHoldingStatusService()
    {
        if (null === static::$holdingStatusService) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            static::$holdingStatusService = $objectManager->get(HoldingStatusService::class);
        }

        return static::$holdingStatusService;
    }
}
