<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 * Splits a string with parse_url
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CleanUrnLinkViewHelper extends AbstractViewHelper
{
    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('link', 'string', 'URL string', true, null);
    }

    /**
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $link = $arguments['link'];

        if ($link === null) {
            return '';
        }

        if (substr($link, 0, 4) === "urn:") {
            return 'https://nbn-resolving.de/'.$link;
        }

        // HOTFIX
        if (strpos($link, 'lynda.com') !== false) {
            return $link.'?org=slub-dresden.de';
        }

        // HOTFIX
        if (strpos($link, 'ezeit') !== false) {
            return $link.'&bibid=SLUB';
        }

        return 'https://wwwdb.dbod.de/login?url='.$link;
    }
}
