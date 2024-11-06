<?php

namespace Slub\SlubFindExtend\ViewHelpers\Link;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class GetPrefixedLinkViewHelper extends AbstractViewHelper
{

    /**
     * The prefix string
     * @var string
     */
    const PREFIX = 'https://wwwdb.dbod.de/login?url=';

    /**
     * Hosts that should not be prefixed
     * @var array
     */
    const NO_PREFIX_HOSTS = ['dbis.uni-regensburg.de', 'www.bibliothek.uni-regensburg.de','ezb.ur.de', 'wwwdb.dbod.de', 'www.dbod.de', 'nbn-resolving.de', 'digital.slub-dresden.de', 'digital.zlb.de', 'www.deutschefotothek.de', 'mediathek.slub-dresden.de'];

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('link', 'string|array', 'The link to check', true);
    }

    /**
     * Render the link with prefix
     * 
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $link = $arguments['link'];

        if(!is_string($link)) {
            return $link;
        }   

        $prefix = self::PREFIX;
        $note = '';

        $linkParsed = parse_url($link);

        if (in_array($linkParsed['host'], self::NO_PREFIX_HOSTS)) {
            $prefix =  '';
        }

        if(str_ends_with($link, 'manifest.json')) {
            $note = 'IIIF-Manifest';
        }

        return array(
            "uri" => $link,
            "prefix" => $prefix,
            "note" => $note
        );
    }
}
