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
     * @return array
     */
    public function getLinks($fullrecord, $isil = NULL)
    {

        $isilLinks = [];
        $titleLinks = [];

        $reference = $this->marcRefrenceResolverService->resolveReference('856', $fullrecord);

        for ($i = 0; $i < count($reference->cache["856"]); $i++) {

            if (count($isil) > 0) {

                $note = '';

                if ($reference->cache["856[" . $i . "]"]->getSubfield('z')) {
                    $note = $reference->cache["856[" . $i . "]"]->getSubfield('z')->getData();
                } elseif ($reference->cache["856[" . $i . "]"]->getSubfield('3')) {
                    $note = $reference->cache["856[" . $i . "]"]->getSubfield('3')->getData();
                }

                if($reference->cache["856[" . $i . "]"]->getSubfield('u')) {
                    $uri = trim($reference->cache["856[" . $i . "]"]->getSubfield('u')->getData());
                    if (substr($uri, 0, 4) === "urn:") {
                        $uri = 'http://nbn-resolving.de/' . $uri;
                    }

                    if ($reference->cache["856[" . $i . "]"]->getSubfield('9') && in_array($reference->cache["856[" . $i . "]"]->getSubfield('9')->getData(), $isil)) {
                        if (!$this->in_array_field($uri, 'uri', $isilLinks)) {
                            $isilLinks[] = ["uri" => $uri, "note" => $note];
                        }
                    } elseif (!$reference->cache["856[" . $i . "]"]->getSubfield('9')) {
                        if (!$this->in_array_field($uri, 'uri', $titleLinks)) {
                            $titleLinks[] = ["uri" => $uri, "note" => $note];
                        }
                    }
                }

            }

        }

        if (count($isil) && count($isilLinks)) {
            return $isilLinks;
        }

        return $titleLinks;

    }

    /**
     * To check the if uri exits
     */
    private function in_array_field($needle, $needle_field, $haystack)
    {
        foreach ($haystack as $item)
            if (isset($item[$needle_field]) && $item[$needle_field] == $needle)
                return true;
        return false;
    }

}
