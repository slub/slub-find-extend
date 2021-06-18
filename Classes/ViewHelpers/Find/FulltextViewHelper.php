<?php

namespace Slub\SlubFindExtend\ViewHelpers\Find;

/**
 *
 */

use Slub\SlubFindExtend\Services\FulltextService;
use Solarium\QueryType\Select\Result\Document;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class FulltextViewHelper extends AbstractViewHelper
{

	/**
	 * @var FulltextService
	 */
	protected static $fulltextService = null;

	/**
	 * Registers own arguments.
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('document', '\Solarium\QueryType\Select\Result\Document', 'Result document', TRUE);
	}

    /**
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        return static::getFulltextService()->getFulltextLink($arguments['document']);
    }

    private static function getFulltextService()
    {
        if (null === static::$fulltextService) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            static::$fulltextService = $objectManager->get(FulltextService::class);
        }

        return static::$fulltextService;
    }

}
