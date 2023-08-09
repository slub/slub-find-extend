<?php

namespace Slub\SlubFindExtend\ViewHelpers\Find;

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

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ParseHighlightedFieldViewHelper extends AbstractViewHelper {

    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Registers arguments.
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('data', 'string', 'field with highlighting to get parsed', true);
        $this->registerArgument('highlightTagOpen', 'string', 'opening tag to insert to begin highlighting', false, '<em class="highlight">');
        $this->registerArgument('highlightTagClose', 'string', 'closing tag to insert to end highlighting', false, '</em>');
    }

    /**
     *
     * @return string
     */

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        if (empty($arguments['data'])) {
            return '';
        } else {
            return self::highlightData($arguments['data'], $arguments);
        }
    }

    protected static function highlightData($data, $arguments) {
        $result = '';
        $result = str_replace(['\ueeee', '\ueeef'],[$arguments['highlightTagOpen'], $arguments['highlightTagClose']], $data);

        return $result;
    }
}

?>
