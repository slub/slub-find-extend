<?php

namespace Slub\SlubFindExtend\Services;

/**
 * Class StopWordService
 * @package Slub\SlubFindExtend\Services
 */
class StopWordService
{
    /**
     * @return array
     */
    private function getStopWords()
    {
        return array('A','ALS','AM','AN','AND','ARE','AS','AT','AUF','AUS','BE','BUT','BY','DAS','DASS','DAß','DER','DICH','DIE','DIR','DU','DURCH','EINE','EINEM','EINEN','EINER','EINES','ER','ES','FOR','FÜR','IF','IHR','IHRE','IHRES','IM','IN','INTO','IS','IST','IT','KEIN','MEIN','MICH','MIR','MIT','NO','NOT','ODER','OF','OHNE','ON','OR','S','SEIN','SIE','SUCH','T','THAT','THE','THEIR','THEN','THERE','THESE','THEY','THIS','TO','UND','VON','WAR','WAS','WEGEN','WER','WIE','WILL','WIR','WIRD','WITH');
    }

    /**
     * @param string $querystring
     * @return string
     */
    private function stripPuntuations($querystring)
    {
        return str_replace(array(',', '.', ':', ';', '?', '!', '\'', '(', ')', '&', '$', '[', ']'), array(), $querystring);
    }

    /**
     * @param string $querystring
     * @return string
     */
    private function stripStopWords($querystring)
    {
        $querystringPieces = explode(' ', $querystring);

        $querystringPieces = array_diff($querystringPieces, $this->getStopWords());

        return implode(' ', $querystringPieces);
    }

    /**
     * @param string $querystring
     * @return string
     */
    public function cleanQueryString($querystring)
    {
        if (preg_match('/^".*"$/', trim($querystring))) {
            return $querystring;
        }

        return $this->stripStopWords($this->stripPuntuations(mb_strtoupper($querystring, 'UTF-8')));
    }
}
