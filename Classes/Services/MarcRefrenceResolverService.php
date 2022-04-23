<?php

namespace Slub\SlubFindExtend\Services;

use File_MARC_Reference;
use File_MARC_Record;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('slub_find_extend') . 'vendor/autoload.php');

/**
 * Class MarcRefrenceResolverService
 * @package Slub\SlubFindExtend\Services
 */
class MarcRefrenceResolverService
{
    /**
     * Resiolves a reference against raw data
     *
     * @param string $path
     * @param object $record
     * @param boolean $index
     * @return array|boolean
     */
    public function resolveReference($path, $record, $index = null)
    {
        if (!$record instanceof \File_MARC_Record) {
            return false;
        }

        $reference = new File_MARC_Reference($path, $record);

        if ($index !== null && is_array($reference->content)) {
            return $reference->content[$index];
        } else {
            return $reference;
        }
    }
}
