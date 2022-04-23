<?php

namespace Slub\SlubFindExtend\Services;

use Solarium\QueryType\Select\Result\Document;
use Slub\SlubFindExtend\Services\MarcRefrenceResolverService;

/**
 * Class StatusService
 * @package Slub\SlubFindExtend\Services
 */
class LinksFromMarcFullrecordService
{
    /**
     * @var \Slub\SlubFindExtend\Services\MarcRefrenceResolverService
     * @inject
     */
    protected $marcRefrenceResolverService;

    /**
     * Returns the links from the MARC fullrecord
     *
     * @param object $fullrecord
     * @param array $isil
     * @param boolean $unique
     * @param boolean $merged
     * @return array
     */
    public function getLinks($fullrecord, $isil = null, $unique = false, $merged = false)
    {
        $defaultPrefix = 'http://wwwdb.dbod.de/login?url=';
        $noPrefixHosts = ['wwwdb.dbod.de', 'www.dbod.de', 'nbn-resolving.de', 'digital.slub-dresden.de', 'digital.zlb.de', 'www.deutschefotothek.de'];
        $blacklistLabel = ['Kostenfrei', 'Volltext'];

        $resourceLinks = [];
        $relatedLinks = [];
        $isilLinks = [];
        $unspecificLinks = [];

        $reference = $this->marcRefrenceResolverService->resolveReference('856', $fullrecord);

        for ($i = 0; $i < count($reference->cache["856"]); $i++) {
            $prefix = $defaultPrefix;
            $note = '';
            $material = '';
            $ind1 = $reference->cache["856[" . $i . "]"]->getIndicator(1);
            $ind2 = $reference->cache["856[" . $i . "]"]->getIndicator(2);

            if ($reference->cache["856[" . $i . "]"]->getSubfield('u')) {
                $uri = trim($reference->cache["856[" . $i . "]"]->getSubfield('u')->getData());

                if (substr($uri, 0, 4) === "urn:") {
                    $uri = 'http://nbn-resolving.de/' . $uri;
                }
                $uri = str_replace('https://wwwdb.dbod.de/login?url=', '', $uri);

                $uriParsed = parse_url($uri);

                if (in_array($uriParsed['host'], $noPrefixHosts)) {
                    $prefix =  '';
                }

                if ($reference->cache["856[" . $i . "]"]->getSubfield('z')) {
                    $note = $reference->cache["856[" . $i . "]"]->getSubfield('z')->getData();
                    if (in_array($note, $blacklistLabel)) {
                        $note = '';
                    }
                }
                if ($reference->cache["856[" . $i . "]"]->getSubfield('3')) {
                    $material = $reference->cache["856[" . $i . "]"]->getSubfield('3')->getData();
                    $material = str_replace('#', ' - ', $material);
                    if (in_array($material, $blacklistLabel)) {
                        $material = '';
                    }
                }

                if ($reference->cache["856[" . $i . "]"]->getSubfield('9') && in_array($reference->cache["856[" . $i . "]"]->getSubfield('9')->getData(), $isil)) {
                    if ($reference->cache["856[" . $i . "]"]->getSubfield('9')->getData() === 'LFER') {
                        $prefix =  '';
                    }

                    $linkNotInArray = true;
                    if ($unique) {
                        $linkNotInArray = !is_int(array_search($uri, array_column($isilLinks, 'uri')));
                    }

                    if ($linkNotInArray) {
                        $isilLinks[] = ["uri" => $uri, "note" => $note, "material" => $material, "prefix" => $prefix];
                    }
                } elseif (($ind1 === '4') && ($ind2 === '2')) {
                    $linkNotInArray = true;
                    if ($unique) {
                        $linkNotInArray = !is_int(array_search($uri, array_column($relatedLinks, 'uri')));
                    }

                    if ($linkNotInArray) {
                        $relatedLinks[] = ["uri" => $uri, "note" => $note, "material" => $material, "prefix" => ''];
                    }
                } elseif (($ind1 === '4') && ($ind2 === '0')) {
                    $linkNotInArray = true;
                    if ($unique) {
                        $linkNotInArray = !is_int(array_search($uri, array_column($resourceLinks, 'uri')));
                    }

                    if ($linkNotInArray) {
                        $resourceLinks[] = ["uri" => $uri, "note" => $note, "material" => $material, "prefix" => $prefix];
                    }
                } else {
                    $linkNotInArray = true;
                    if ($unique) {
                        $linkNotInArray = !is_int(array_search($uri, array_column($unspecificLinks, 'uri')));
                    }

                    if ($linkNotInArray) {
                        $unspecificLinks[] = ["uri" => $uri, "note" => $note, "material" => $material, "prefix" => $prefix];
                    }
                }
            }
        }

        if ((sizeof($isilLinks) == 0) && (sizeof($relatedLinks) == 0) && (sizeof($relatedLinks) == 0) && (sizeof($unspecificLinks) > 0)) {
            $resourceLinks = $unspecificLinks;
        }

        if ($merged) {
            return array_merge($isilLinks, $resourceLinks, $relatedLinks);
        } else {
            return [
                'isil' => $isilLinks,
                'resource' => $resourceLinks,
                'related' => $relatedLinks
            ];
        }
    }
}
