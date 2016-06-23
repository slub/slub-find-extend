<?php

namespace Slub\SlubFindExtend\Services;

/**
 * Class StopWordService
 * @package Slub\SlubFindExtend\Services
 */
class StopWordService {

    /**
     * @return array
     */
    private function getStopWords() {

        return array('A','AAN','ALS','AN','AT','AU','AUS','BY','D','DAS','DE','DEM','DEN','DER','DES','DET','DIE','DU','E','EEN','EIN','EINE','EINEM','EINEN','EINER','EINES','EN','ET','ETT','FOR','FROM','FÜR','HET','IM','IN','L','LA','LE','LES','MET','MIT','N','NAAR','OF','ON','OP','OVER','POUR','S','T','THE','TO','U','ÜBER','UIT','UND','UNE','VOM','VOOR','VOR','WITH','ZU','ZUM','ZUR');

    }

    /**
     * @param string $querystring
     * @return string
     */
    private function stripPuntuations($querystring) {

        return str_replace(array(',', '.', ':', ';', '?', '!', '\'', '(', ')', '&', '$', '[', ']'), array(), $querystring);

    }

    /**
     * @param string $querystring
     * @return string
     */
    private function stripStopWords($querystring) {

        $querystringPieces = explode(' ', $querystring);

        $querystringPieces = array_diff($querystringPieces, $this->getStopWords());

        return implode(' ', $querystringPieces);
    }

    /**
     * @param string $querystring
     * @return string
     */
    public function cleanQueryString($querystring) {

        if(preg_match('/^".*"$/', trim($querystring))) { return $querystring; }

        return $this->stripStopWords($this->stripPuntuations(strtoupper($querystring)));

    }

}