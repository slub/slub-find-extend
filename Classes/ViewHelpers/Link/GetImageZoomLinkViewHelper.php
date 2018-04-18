<?php
namespace Slub\SlubFindExtend\ViewHelpers\Link;

  /***************************************************************
   *
   *  Copyright notice
   *
   *  This script is part of the TYPO3 project. The TYPO3 project is
   *  free software; you can redistribute it and/or modify
   *  it under the terms of the GNU General Public License as published by
   *  the Free Software Foundation; either version 3 of the License, or
   *  (at your option) any later version.
   *
   *  The GNU General Public License can be found at
   *  http://www.gnu.org/copyleft/gpl.html.
   *
   *  This script is distributed in the hope that it will be useful,
   *  but WITHOUT ANY WARRANTY; without even the implied warranty of
   *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   *  GNU General Public License for more details.
   *
   *  This copyright notice MUST APPEAR in all copies of the script!
   ***************************************************************/

class GetImageZoomLinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        $this->registerArgument('data', 'string', 'The data', TRUE, NULL);
        $this->registerArgument('sourceId', 'string', 'The sourceId field from SOLR', TRUE, NULL);
    }

    /**
     * maps to different zoom image resources
     *
     * @return string
     */

    public function render() {
        $content = $this->renderChildren();

        $linkContent = '<a class="zoom-link" onClick="showZoomOverlay(this);">'.$content.'</a>';

        if ( $this->arguments['sourceId'] == '67' || $this->arguments['sourceId'] == '67-ahn' || $this->arguments['sourceId'] == '67-slub' ) {
            $linkContent = '<a class="zoom-link" target="_blank" href="'.$this->getFotohekZoomUrl($recordId).'">'.$content.'</a>';
        }
        return $linkContent;
    }

    protected function getFotohekZoomUrl($recordId) {
        // deutschefotothek
        // record_id => 'oai::a8450::obj|80111251|df_hauptkatalog_0739734'
        // http://www.deutschefotothek.de/documents/obj/80111251/df_hauptkatalog_0739734
        $parts = explode('|',$this->arguments['data']);
        return 'http://www.deutschefotothek.de/documents/obj/' . $parts[1] . '/' . $parts[2];
    }

}
?>