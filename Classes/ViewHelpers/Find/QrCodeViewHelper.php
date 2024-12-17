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

        $imageDirWebroot = 'typo3temp/slub_find_extend/qr/';
        $imageDirAbs = GeneralUtility::getFileAbsFileName($imageDirWebroot);
        $imageName = MD5($arguments['url']).'.svg';

        if(!is_dir($imageDirAbs)) {
            GeneralUtility::mkdir_deep($imageDirAbs);
        }

        $writer->writeFile($arguments['url'], $imageDirAbs.$imageName);
        GeneralUtility::fixPermissions($imageDirAbs.$imageName);

        return '/'.$imageDirWebroot.$imageName;
    }


}
