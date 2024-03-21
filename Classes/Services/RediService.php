<?php

namespace Slub\SlubFindExtend\Services;

use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Class RedisService
 * @package Slub\SlubFindExtend\Services
 */
class RediService
{

    public function getCached($document, $enriched) 
    {
        $cache = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('resolv_link_electronic');
        $cacheIdentifier = sha1($document['id']);
        $entry = $cache->get($cacheIdentifier);
        if (!$entry) {
            // Try to resolve article against redi
            $entry = $this->getElectronicHoldingFromData($document, $enriched);
            $cache->set($cacheIdentifier, $entry);
        }

        return $entry;
    }

    /**
     * Tries to resolve Article against holdings
     *
     */
    private function getElectronicHoldingFromData($document, $enriched)
    {
        $status = [];

        if (!$enriched) {
            return;
        }

        $article = $enriched['fields']['rft.atitle'];
        $firstISSN = $enriched['fields']['rft.issn'][0];
        $volume = $enriched['fields']['rft.volume'];
        $spage = $enriched['fields']['rft.spage'];
        $epage = $enriched['fields']['rft.epage'];
        $pages = $enriched['fields']['rft.pages'];
        $issue = $enriched['fields']['rft.issue'];
        $genre = $enriched['fields']['rft.genre'];
        $date = $enriched['fields']['rft.date'];
        $language = $enriched['fields']['languages'][0];
        $doi = $enriched['fields']['doi'];
        $jtitle = $enriched['fields']['rft.jtitle'];
        $firstAuthor = (array)$enriched['fields']['authors'][0];
        $firstAuthorAulast = $firstAuthor['rft.aulast'];
        $firstAuthorAufirst = $firstAuthor['rft.aufirst'];

        $url = 'http://www-s.redi-bw.de/links/?rl_site=slub&atitle='.urlencode($article).
            '&issn='.urlencode($firstISSN).
            '&volume='.urlencode($volume).
            '&spage='.urlencode($spage).
            '&epage='.urlencode($epage).
            '&pages='.urlencode($pages).
            '&issue='.urlencode($issue).
            '&aulast='.urlencode($firstAuthorAulast).
            '&aufirst='.urlencode($firstAuthorAufirst).
            '&genre='.urlencode($genre).
            '&sid=katalogbeta.slub-dresden.de&date='.urlencode($date).
            '&language='.urlencode($language).
            '&id='.urlencode($doi).
            '&title='.urlencode($jtitle);

        $doc = new \DOMDocument();
        $html = $this->getData($url);
        if (strlen($html) === 0) {
            return;
        }

        libxml_use_internal_errors(true);
        @$doc->loadHTML($html);

        $xpath = new \DOMXpath($doc);

        $infolink = $xpath->query("//span[contains(@class,'t_infolink')]/a/@href")->item(0)->nodeValue;

        $access = $xpath->query("//div[@id ='t_ezb']/div/p/b")->item(0)->nodeValue;

        $doilink = $xpath->query("//dd[contains(@class,'doi_d')]/span/a/@href")->item(0)->nodeValue;

        $status_code = 10;
        $url = '';
        $via = '';

        for ($i = 0; $i < $xpath->query("//div[@id ='t_ezb']/div/div[contains(@class,'t_ezb_result')]/p")->length; $i++) {
            $ezb_status_code = 10;

            $ezb_status = $xpath->query("//div[@id ='t_ezb']/div/div[contains(@class,'t_ezb_result')]/p/span[contains(@class, 't_ezb_yellow') or contains(@class, 't_ezb_green') or contains(@class, 't_ezb_red')]/@class")->item($i)->nodeValue;
            $ezb_status_via = trim($xpath->query("//div[@id ='t_ezb']/div/div[contains(@class,'t_ezb_result')]/p")->item($i)->nodeValue);
            $ezb_url = $xpath->query("//div[@id ='t_ezb']/div/div[contains(@class,'t_ezb_result')]/p/span[contains(@class,'t_link')]/a/@href")->item($i)->nodeValue;


            $ezb_via = substr($ezb_status_via, strpos($ezb_status_via, 'via')+4, -4);

            switch ($ezb_status) {
                case 't_ezb_green':
                    $ezb_status_code = 0;
                    break;
                case 't_ezb_yellow':
                    $ezb_status_code = 2;
                    break;
                case 't_ezb_red':
                    $ezb_status_code = 4;
                    break;
            }

            if ($ezb_status_code < $status_code) {
                $status_code = $ezb_status_code;
                $via = $ezb_via;
                $url = $ezb_url;
            }
        }

        $oa_url = $xpath->query("//div[@id ='t_oadoi']/div/div[contains(@class,'t_ezb_result')]/p/span[contains(@class,'t_link')]/a/@href")->item(0)->nodeValue;

        if (strlen($oa_url) > 0) {
            $oa_via = trim($xpath->query("//div[@id ='t_oadoi']/div/div[contains(@class,'t_ezb_result')]/p")->item(0)->nodeValue);
            $oa_via = substr($oa_via, strpos($oa_via, 'via')+4, strlen($oa_via)-strpos($oa_via, ',')-5);
        }

        $status['infolink'] = $infolink;
        $status['access'] = $access == 'freigeschaltet' ? 1 : 0;
        $status['via'] = $via;
        $status['url'] = $url;
        $status['status'] = $status_code;
        $status['oa_url'] = $oa_url;
        $status['oa_via'] = $oa_via;
        $status['doilink'] = $doilink;

        return $status;
    }

    private function getData($url)
    {
        $ch = curl_init();
        $timeout = 10;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}

