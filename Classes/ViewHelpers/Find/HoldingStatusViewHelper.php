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

class HoldingStatusViewHelper extends AbstractViewHelper
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
            return static::getHoldingStatusService()->getStatus($arguments['document'], $arguments['copies']);
        } else {
            return 0;
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
