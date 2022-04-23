<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * GetRvkTextViewHelper
 */
class GetRvkTextViewHelper extends AbstractViewHelper
{
    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('rvk', 'string', 'The rvk value to resolve', false, null);
    }

    /**
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $rvk = $arguments['rvk'];

        if ($rvk === null) {
            $rvk = $renderChildrenClosure();
        }

        $url = 'http://sdvkatalogrvk.slub-dresden.de/api/?rvk='.urlencode(trim($rvk));

        $rvkArray = json_decode(static::getData($url), true);

        if (!empty($rvkArray["name"])) {
            return trim($rvk) . ' : ' . $rvkArray["name"];
        }

        return $rvk;
    }

    private static function getData($url)
    {
        $ch = curl_init();
        $timeout = 10;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
