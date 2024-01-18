<?php

namespace Slub\SlubFindExtend\ViewHelpers\Link;

use Slub\SlubFindExtend\Services\MarcRefrenceResolverService;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use File_MARC_Record;
use File_MARC_Reference;
class LinksFromDataViewHelper extends AbstractViewHelper
{

    /**
     * @var \Solarium\Client
     */
    protected static $solr = null;

    /**
     * @var MarcRefrenceResolverService
     */
    protected static $marcRefrenceResolverService = null;

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('marc', 'string', 'The raw MARC', false, null);
        $this->registerArgument('document', 'array', 'The Solr doc', false, null);

    }

    /**
     * Render the link with prefix
     * 
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        $templateVariableContainer = $renderingContext->getVariableProvider();

        $return_links = array();
        $return_links['access'] = array();
        $return_links['additional_information'] = array();
        $return_links['references'] = array();
        $return_links['links'] = array();

        /** 
         * Link besteht aus url, url_prefix, label, intro, material, note
         */

        $isil_links = array();
        $isil_links = $arguments['document']['url_de14_str_mv'];
        $has_isil_links = false;
        if(count($isil_links) > 0) {
            $has_isil_links = true;
        }

        if($has_isil_links) {
            foreach($isil_links as $isil_link) {

                $url = parse_url($isil_link);

                $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.' . $url['host'];
                $localisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) : '';      

                if (str_ends_with($isil_link, 'manifest.json')) {

                    $return_links['access'][] = array(
                        'url' => $isil_link,
                        'url_prefix' => '',
                        'label' => $localisedLabel,
                        'intro' => '',
                        'material' => 'iiif',
                        'note' => ''
                    );

                } else {
                    $return_links['access'][] = array(
                        'url' => $isil_link,
                        'url_prefix' => '',
                        'label' => $localisedLabel,
                        'intro' => '',
                        'material' => '',
                        'note' => ''
                    );

                }


                
            }

            // Find iiif manifests            
            foreach($arguments['document']['url'] as $document_url) {
                if (str_ends_with($document_url, 'manifest.json')) {

                    if(!in_array($document_url, $isil_links)) {

                        $return_links['access'][] = array(
                            'url' => 'https://iiif.arthistoricum.net/mirador/?id='.$document_url,
                            'url_prefix' => '',
                            'label' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.iiif.arthistorricum'),
                            'intro' => '',
                            'material' => 'iiif',
                            'note' => ''
                        );

                        $return_links['access'][] = array(
                            'url' => $document_url,
                            'url_prefix' => '',
                            'label' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.iiif.manifest'),
                            'intro' => '',
                            'material' => '',
                            'note' => ''
                        );

                    }

                }
                
            }

        }

        $marc = $arguments['marc'];
        $document = $arguments['document'];

        $decoder = new \Slub\SlubFindExtend\Slots\Decoder\Marc21();
        /** @var \File_MARC_Record */
        $decoded = $decoder->decode($marc);

        /** @var \Object */
        $reference = static::getMarcRefrenceResolverService()->resolveReference('856', $decoded);

        for ($i = 0; $i < count($reference->cache["856"]); $i++) {

            $ind1 = $reference->cache["856[" . $i . "]"]->getIndicator(1);
            $ind2 = $reference->cache["856[" . $i . "]"]->getIndicator(2);

            if(($ind1 == 4) && ($ind2 == 0)) {

                if(!$has_isil_links) {
                    if ($reference->cache["856[" . $i . "]"]->getSubfield('u')) {
                        $uri = trim($reference->cache["856[" . $i . "]"]->getSubfield('u')->getData());

                        $return_links['access'][] = array(
                            'url' => $uri,
                            'url_prefix' => '',
                            'label' => '',
                            'intro' => '',
                            'material' => '',
                            'note' => ''
                        );
                    }
                }

            }

            if(($ind1 == 4) && ($ind2 == 1) || 
               ($ind1 == 4) && ($ind2 == 2)) {

                if ($reference->cache["856[" . $i . "]"]->getSubfield('u')) {
                    $uri = trim($reference->cache["856[" . $i . "]"]->getSubfield('u')->getData());

                    if (!str_ends_with($document_url, 'manifest.json')) {

                        $return_links['additional_information'][] = array(
                            'url' => $uri,
                            'url_prefix' => '',
                            'label' => '',
                            'intro' => '',
                            'material' => 'iiif',
                            'note' => ''
                        );
    
                    }
                }
            }

            
        }
   
        static::getReferenceFromMarcField('770_08', $decoded, $templateVariableContainer, $return_links);

        static::getReferenceFromMarcField('772_08', $decoded, $templateVariableContainer, $return_links);

        static::getReferenceFromMarcField('775_08', $decoded, $templateVariableContainer, $return_links);

        static::getReferenceFromMarcField('776_08', $decoded, $templateVariableContainer, $return_links);

        static::getReferenceFromMarcField('780_0', $decoded, $templateVariableContainer, $return_links);

        static::getReferenceFromMarcField('785_0', $decoded, $templateVariableContainer, $return_links);

        static::getReferenceFromMarcField('787_0', $decoded, $templateVariableContainer, $return_links);

        /** @var \Object */
        $reference = static::getMarcRefrenceResolverService()->resolveReference('024_7', $decoded);

        for ($i = 0; $i < count($reference->cache["024_7"]); $i++) {
            if ($reference->cache["024_7"][$i]->getSubfield('2')->getData() == 'vd16') {

                $return_links['references'][] = array(
                    'url' => 'http://gateway-bayern.de/'.urlencode($reference->cache["024_7"][$i]->getSubfield('a')->getData()),
                    'url_prefix' => '',
                    'label' => '',
                    'intro' => '',
                    'material' => '',
                    'note' => ''
                );

            }
            if ($reference->cache["024_7"][$i]->getSubfield('2')->getData() == 'vd17') {

                $return_links['references'][] = array(
                    'url' => 'https://kxp.k10plus.de/DB=1.28/CMD?ACT=SRCHA&IKT=8079&TRM=%27'.trim(ltrim($reference->cache["024_7"][$i]->getSubfield('a')->getData(), 'VD17')).'%27',
                    'url_prefix' => '',
                    'label' => '',
                    'intro' => '',
                    'material' => '',
                    'note' => ''
                );

            }
        }

        if (in_array("Sächsische Bibliografie", $document['mega_collection'])) {
            $return_links['references'][] = array(
                'url' => 'https://swb.bsz-bw.de/DB=2.304/PPNSET?PPN='.$document['kxp_id_str'],
                'url_prefix' => '',
                'label' => '',
                'intro' => '',
                'material' => '',
                'note' => ''
            );
        }

        if(!$has_isil_links) {

            foreach($document['url'] as $url) {
                if((strpos($url, '|')) && ($document['source_id'] != '215')) {
                    $url_parts = explode('|', $url);
                    $return_links['references'][] = array(
                        'url' => $url_parts[1],
                        'url_prefix' => '',
                        'label' => $url_parts[0],
                        'intro' => '',
                        'material' => '',
                        'note' => ''
                    );
                } else {

                    if(strpos($url, '|')) {
                        $url_parts = explode('|', $url);
                        $return_links['links'][] = array(
                            'url' => $url_parts[1],
                            'url_prefix' => '',
                            'label' => $url_parts[0],
                            'intro' => '',
                            'material' => '',
                            'note' => ''
                        );
                    } else {
                        $return_links['links'][] = array(
                            'url' => $url,
                            'url_prefix' => '',
                            'label' => '',
                            'intro' => '',
                            'material' => '',
                            'note' => ''
                        );
                    }

                }
            }

        }



        return $return_links;
    }

    private static function getMarcRefrenceResolverService()
    {
        if (null === static::$marcRefrenceResolverService) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            static::$marcRefrenceResolverService = $objectManager->get(MarcRefrenceResolverService::class);
        }

        return static::$marcRefrenceResolverService;
    }

    /**
     * @return \Solarium\Client
     */
    private static function getSolariumClient()
    {
        if (null === static::$solr) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            static::$solr = $objectManager->get(\Solarium\Client::class);
        }

        return static::$solr;
    }

    private static function getSolariumClientOptionsArray(&$templateVariableContainer, $query)
    {
        $configuration = array(
            'endpoint' => array(
                'localhost' => array(
                    'host' => $templateVariableContainer->get('settings')['connection']['host'],
                    'port' => intval($templateVariableContainer->get('settings')['connection']['port']),
                    'path' => $templateVariableContainer->get('settings')['connection']['path'],
                    'timeout' => $templateVariableContainer->get('settings')['connection']['timeout'],
                    'scheme' => $templateVariableContainer->get('settings')['connection']['scheme']
                )
            ),
            'solarium' => $query
        );

        return $configuration;
    }

    /**
     * Creates a query for a document
     *
     * @param string $id the document id
     * @param string $idfield the document id field
     * @return \Solarium\QueryType\Select\Query\Query
     */
    private static function createQuery($solrClient, $query, &$templateVariableContainer)
    {
        $queryObject = $solrClient->createSelect();
        static::addTypoScriptFilters($queryObject, $templateVariableContainer);

        $queryObject->setQuery($query);

        static::createQueryComponents($queryObject, $templateVariableContainer);

        return $queryObject;
    }

    /**
     * Adds filter queries configured in TypoScript to $query.
     *
     * @param \Solarium\QueryType\Select\Query\Query $query
     */
    private static function addTypoScriptFilters(&$query, &$templateVariableContainer)
    {
        if (!empty($templateVariableContainer->get('settings')['additionalFilters'])) {
            foreach ($templateVariableContainer->get('settings')['additionalFilters'] as $key => $filterQuery) {
                $query->createFilterQuery('additionalFilter-' . $key)
                    ->setQuery($filterQuery);
            }
        }
    }

    /**
     * Check configuration for shards and when found create Distributed Search
     * @param \Solarium\QueryType\Select\Query\Query $query
     */
    private static function createQueryComponents(&$query, &$templateVariableContainer)
    {

        // Shards
        if (is_array($templateVariableContainer->get('settings')['shards']) && count($templateVariableContainer->get('settings')['shards'])) {
            $distributedSearch = $query->getDistributedSearch();
            foreach ($templateVariableContainer->get('settings')['shards'] as $name => $shard) {
                $distributedSearch->addShard($name, $shard);
            }
        }
    }

    private static function getReferenceFromMarcField($selector, $decoded, $templateVariableContainer, &$return_links) {

        $solrClient = static::getSolariumClient();

        /** @var \Object */
        $reference = static::getMarcRefrenceResolverService()->resolveReference($selector, $decoded);

        for ($i = 0; $i < count($reference->cache[$selector]); $i++) {

            if ($reference->cache[$selector][$i]->getSubfield('w')) {

                $id = ltrim($reference->cache[$selector][$i]->getSubfield('w')->getData(), '(DE-627)');
            
                $query = static::createQuery($solrClient, 'kxp_id_str:"'.$id.'"', $templateVariableContainer);
                $solrClient->setOptions(static::getSolariumClientOptionsArray($templateVariableContainer, $query));

                /** @var Result $resultSet */
                $resultSet = static::$solr->select($query);

                /** @var DocumentInterface $result */
                $results = $resultSet->getDocuments();

                if ($results) {
                    /** @var \Solarium\QueryType\Select\Result\Document */
                    $result = $results[0];

                    $return_links['references'][] = array(
                        'url' => '/id/'.$result->getFields()['id'],
                        'url_prefix' => '',
                        'label' => '',
                        'intro' => '',
                        'material' => '',
                        'note' => ''
                    );

                }
            }

        }

    }
}