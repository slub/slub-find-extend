<?php

namespace Slub\SlubFindExtend\Services;

use Solarium\QueryType\Select\Result\Document;

/**
 * Class FulltextService
 * @package Slub\SlubFindExtend\Services
 */
class FulltextService
{
    public const RESOLVER_BASE = '//wwwdb.dbod.de/login?url=%s';

    /**
     * @param Document $document
     * @param \File_MARC_Record $record NULL
     * @return bool|string
     */
    public function getFulltextLink(Document $document, $record = null)
    {
        if ((in_array('Online', $document['facet_avail'])) && (strlen($document['url'][0]) > 0)) {
            return sprintf(self::RESOLVER_BASE, $document['url'][0]);
        } elseif (($document['format'][0] === 'Electronic Resource (Remote Access)') && (strlen($document['url'][0]) > 0)) {
            return sprintf(self::RESOLVER_BASE, $document['url'][0]);
        }

        return false;
    }
}
