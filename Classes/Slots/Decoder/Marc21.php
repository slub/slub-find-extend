<?php
namespace Slub\SlubFindExtend\Slots\Decoder;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use File_MARC;
use File_MARC_Record;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('slub_find_extend') . 'vendor/autoload.php');


/**
 * Slot implementation before the
 *
 * @category    Decoder
 * @package     TYPO3
 */

class Marc21 {

    /**
     * Decodes raw Marc21 to File_MARC_Record
     *
     * @param string $marc
     * @return File_MARC_Record
     */
    public function decode($marc) {

        $marc = str_replace(
            ['#29;', '#30;', '#31;'], ["\x1D", "\x1E", "\x1F"], $marc
        );

        $records = new File_MARC($marc, File_MARC::SOURCE_STRING);

        /** @var \File_MARC_Record $record */
        $record = $records->next();

        return $record;

    }

}