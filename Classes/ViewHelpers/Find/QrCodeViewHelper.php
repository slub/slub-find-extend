<?php

namespace Slub\SlubFindExtend\ViewHelpers\Find;

/**
 * Class QrCodeViewHelper
 * 
 * This class generates a QR code from a given URL.
 * 
 * @package Slub\SlubFindExtend\ViewHelpers\Find
 */


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('slub_find_extend') . 'vendor/autoload.php');

class QrCodeViewHelper extends AbstractViewHelper
{

    /**
     * Registers own arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('url', 'string', 'URL to enocode to qr code', true);
    }

 
    /**
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        $renderer = new \BaconQrCode\Renderer\Image\Svg();
        $renderer->setHeight(256);
        $renderer->setWidth(256);
        $writer = new \BaconQrCode\Writer($renderer);

        $imageDir = GeneralUtility::getFileAbsFileName('typo3temp/slub_find_extend/qr/');

        if(!is_dir($imageDir)) {
            mkdir($imageDir, 0777, true);
        }

        $writer->writeFile($arguments['url'], GeneralUtility::getFileAbsFileName($imageDir.MD5($arguments['url']).'.svg'));

        return '/typo3temp/slub_find_extend/qr/'.MD5($arguments['url']).'.svg';
    }


}
