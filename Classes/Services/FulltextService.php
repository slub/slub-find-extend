<?php

namespace Slub\SlubFindExtend\Services;

use Solarium\QueryType\Select\Result\Document;

/**
 * Class FulltextService
 * @package Slub\SlubFindExtend\Services
 */
class FulltextService {

    const RESOLVER_BASE = 'http://wwwdb.dbod.de/login?url=%s';

    /**
     * @param Document $document
     * @return bool|string
     */
    public function getFulltextLink(Document $document) {

        if(($document['access_facet'] === 'Electronic Resources') && (strlen($document['url'][0]) > 0)) {

            return sprintf(self::RESOLVER_BASE, $document['url'][0]);

        } elseif (($document['format'][0] === 'Electronic Resource (Remote Access)') && (strlen($document['url'][0]) > 0)) {
            return sprintf(self::RESOLVER_BASE, $document['url'][0]);
        }

        return false;

    }

}
