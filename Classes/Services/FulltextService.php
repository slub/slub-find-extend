<?php

namespace Slub\SlubFindExtend\Services;

use Solarium\QueryType\Select\Result\Document;

/**
 * Class FulltextService
 * @package Slub\SlubFindExtend\Services
 */
class FulltextService {

    const RESOLVER_BASE = '//wwwdb.dbod.de/login?url=%s';

    /**
     * @param Document $document
     * @param \File_MARC_Record $record NULL
     * @return bool|string
     */
    public function getFulltextLink(Document $document, $record = NULL) {

        if((in_array($document['access_facet'],['Electronic Resources','Electronic Resource (Remote Access)'])) && (strlen($document['url'][0]) > 0)) {


            return sprintf(self::RESOLVER_BASE, $document['url'][0]);

        }

        return false;

    }

}
