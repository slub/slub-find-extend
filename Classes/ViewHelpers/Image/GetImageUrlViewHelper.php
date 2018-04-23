<?php
namespace Slub\SlubFindExtend\ViewHelpers\Image;

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

class GetImageUrlViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

  /**
  * Get Image by diffrent Hosts
  *
  * @param string $url
  * @param string $size
  * @return string
  */

    public function render($url = NULL, $size = 'original') {

        // default is a 1x1 transparent gif
        $src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";

        $urlP = @parse_url($url);
        if ( $urlP['scheme'] && $urlP['host'] && $urlP['path'] ) {

          // valid url, it's not a validation of image data
          // <todo> if image caching required

            switch ( strtolower($urlP['host']) ) {
                case "fotothek.slub-dresden.de":
                        $src = $this->getFotothekSrc($url, $size);
                    break;
                default:
                    $src = $url;
            }
        }
        return $src;
    }

    protected function getFotothekSrc($url, $size) {
        /* fotothek image formats
            http://fotothek.slub-dresden.de/thumbs/df_hauptkatalog_0739734.jpg
            http://fotothek.slub-dresden.de/mids/df_hauptkatalog_0739734.jpg
            http://fotothek.slub-dresden.de/fotos/df_hauptkatalog_0739734.jpg
        */

        if ( strtolower($size) == 'original' ) {
            $src = str_replace('/thumbs/', '/fotos/', $url);
        } else {
            $src = str_replace('/thumbs/', '/mids/', $url);
        }
        return $src;
    }

}

?>