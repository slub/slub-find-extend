<?php

namespace Slub\SlubFindExtend\ViewHelpers\Format;

/**
 * Returns Resolving link then url is urn
 *
 */
class CleanUrnLinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Splits a string with parse_url
     *
     * @param string $link URL string
     * @return array
     */
    public function render($link = NULL) {

        if($link === NULL) return '';

        if(substr( $link, 0, 4 ) === "urn:") {
            return 'http://nbn-resolving.de/'.$link;
        }

        // HOTFIX
        if (strpos($link,'lynda.com') !== FALSE) {
            return $link.'?org=slub-dresden.de';
        }

        // HOTFIX
        if (strpos($link,'ezeit') !== FALSE) {
            return $link.'&bibid=SLUB';
        }

        return 'http://wwwdb.dbod.de/login?url='.urlencode($link);
    }

}
