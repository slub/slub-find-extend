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
        $is_marc = false;
        if($arguments['marc'] != '') {
            $is_marc = true;
        }

        if($has_isil_links) {
            foreach($isil_links as $isil_link) {

                $url = parse_url($isil_link);

                $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.' . $url['host'];
                $localisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) : '';      

                $introLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.introlabel_access_format.' . $arguments['document']['format_de14'][0];
                $introLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($introLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($introLocalisationKey) : '';      

                $label = $introLocalisedLabel . ((strlen($localisedLabel) > 0) ? ' via ' : '') .$localisedLabel;

                if (str_ends_with($isil_link, 'manifest.json')) {

                    $return_links['access'][] = array(
                        'url' => $isil_link,
                        'url_prefix' => '',
                        'label' => $label,
                        'intro' => '',
                        'url_title' => '',
                        'material' => 'iiif',
                        'note' => ''
                    );

                } else {
                    $return_links['access'][] = array(
                        'url' => $isil_link,
                        'url_prefix' => '',
                        'label' => $label,
                        'intro' => '',
                        'url_title' => '',
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
                            'url_title' => '',
                            'material' => 'iiif',
                            'note' => ''
                        );

                        $return_links['access'][] = array(
                            'url' => $document_url,
                            'url_prefix' => '',
                            'label' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.iiif.manifest'),
                            'intro' => '',
                            'url_title' => '',
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

            if($ind2 === '0') {

                if(!$has_isil_links) {
                    if ($reference->cache["856[" . $i . "]"]->getSubfield('u')) {
                        $raw_url = trim($reference->cache["856[" . $i . "]"]->getSubfield('u')->getData());
                        $url = parse_url($raw_url);

                        $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.' . $url['host'];
                        $localisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) : '';      
        
                        $introLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.introlabel_access_format.' . $arguments['document']['format_de14'][0];
                        $introLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($introLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($introLocalisationKey) : '';      
        
                        $general = $reference->cache["856[" . $i . "]"]->getSubfield('y') ? $reference->cache["856[" . $i . "]"]->getSubfield('y')->getData(): '';
                        $material = $reference->cache["856[" . $i . "]"]->getSubfield('3') ? $reference->cache["856[" . $i . "]"]->getSubfield('3')->getData(): '';
                        $note = $reference->cache["856[" . $i . "]"]->getSubfield('z') ? $reference->cache["856[" . $i . "]"]->getSubfield('z')->getData() : '';

                        $note = '';
                        $j = 0;
                        foreach ($reference->cache["856[" . $i . "]"]->getSubfields('z') as $code => $value) {
                            // Notiz:
                            // In der Katalogisierung häufig verwendete Einleitung die für die Anzeige entfernt wird
                            if($note === 'lizenzpflichtig') {
                                $note = '';
                            } else {
                                $note .= trim(ltrim($value->getData(), '// '));
                            }
                            ++$j;
                            if($j < count($reference->cache["856[" . $i . "]"]->getSubfields('z'))) {
                                $note .=  " ; ";
                            }
                        }

                        $marclabel = $general;
                        if((strlen($marclabel) > 0) && (strlen($material) > 0)) {
                            $marclabel .= ' ; ';
                        }
                        $marclabel .= $material;
                        if((strlen($marclabel) > 0) && (strlen($note) > 0)) {
                            $marclabel .= ' ; ';
                        }
                        $marclabel .= $note;

                        if(str_contains($marclabel, '#')) {
                            $marclabel = str_replace('#', ' - ', $marclabel);
                        }

                        $label = $introLocalisedLabel . ((strlen($localisedLabel) > 0) ? ' via ' : '') . $localisedLabel . ((strlen($marclabel) > 0) ? ' ('.$marclabel.')' : '');

                        $return_links['access'][] = array(
                            'url' => $raw_url,
                            'url_prefix' => '',
                            'label' => $label,
                            'url_title' => '',
                            'intro' => '',
                            'material' => '',
                            'note' => ''
                        );
                    }
                }

            }

            if(($ind2 === '1') || ($ind2 === '2')) {

                if ($reference->cache["856[" . $i . "]"]->getSubfield('u')) {
                    $raw_url = trim($reference->cache["856[" . $i . "]"]->getSubfield('u')->getData());

                    if (!str_ends_with($raw_url, 'manifest.json')) {

                        $url = parse_url($raw_url);

                        $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.' . $url['host'];
                        $localisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) : '';      
 
                        $introLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.introlabel_additional_relationship.' . $ind2;
                        $introLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($introLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($introLocalisationKey) : '';      

                        $note = '';
                        $j = 0;
                        foreach ($reference->cache["856[" . $i . "]"]->getSubfields('z') as $code => $value) {
                            // Notiz:
                            // In der Katalogisierung häufig verwendete Einleitung die für die Anzeige entfernt wird
                            $note .= trim(ltrim($value->getData(), '// '));
                            ++$j;
                            if($j < count($reference->cache["856[" . $i . "]"]->getSubfields('z'))) {
                                $note .=  " ; ";
                            }
                        }
                                        
                        $material = $reference->cache["856[" . $i . "]"]->getSubfield('3') ? $reference->cache["856[" . $i . "]"]->getSubfield('3')->getData(): '';
                        $general = $reference->cache["856[" . $i . "]"]->getSubfield('y') ? $reference->cache["856[" . $i . "]"]->getSubfield('y')->getData(): '';

                        if($note === 'lizenzpflichtig') {
                            $note = '';
                        }                        

                        if($localisedLabel === '') {
                             if(strlen($material) > 0) {
                                $marclabel = $material;
                                if(strlen($note) > 0) {
                                    $marclabel .= ' ('.$note.')';
                                }
                            } else if(strlen($general) > 0) {
                                $marclabel = $general;
                                if(strlen($note) > 0) {
                                    $marclabel .= ' ('.$note.')';
                                }
                                
                            } else if(strlen($note) > 0) {
                                $marclabel = $note;
                            }

                            if(str_contains($marclabel, '#')) {
                                $marclabel = str_replace('#', ' - ', $marclabel);
                            }

                        } else {
                            $marclabel = '';
                        }


                        // Notiz:
                        // Additional Information ohne intro label wenn marclabel vorhanden
                        // wenn nicht dann nur intro label. außer wenn label "kostenfrei" ist
                        if((strlen($marclabel) > 0) && ($marclabel !== "kostenfrei")) {
                            $label = $marclabel . ((strlen($localisedLabel) > 0) ? ' via ' : '') . $localisedLabel;
                        } else {

                            if($marclabel === "kostenfrei") {
                                $label = $introLocalisedLabel . ((strlen($localisedLabel) > 0) ? ' via ' : '') . $localisedLabel . ((strlen($marclabel) > 0) ? ' ('.$marclabel.')' : '');
                            } else {
                                $label = $introLocalisedLabel . ((strlen($localisedLabel) > 0) ? ' via ' : '') . $localisedLabel;
                            }
                            
                        }

                        // Notiz:
                        // Wenn der Link aus Ergänzende Informationen auch schon im Zugangsbereich 
                        // vorhanden ist, dann wird er in den Ergänzenden Informationen nicht gesondert 
                        // aufgeführt. 
                        // Falls in $z Notizen enthalten sind werden die an den Zugangslink ergänzt.
                        $is_accessslink = false;
                        for($k = 0; $k < count($return_links['access']); $k++) {
                            if($raw_url === $return_links['access'][$k]['url']) {
                                $is_accessslink = true;
                                $return_links['access'][$k]['label'] .= ' (' . $note . ')';
                            }
                        }

                        if(!$is_accessslink) {
                            $return_links['additional_information'][] = array(
                                'url' => $raw_url,
                                'url_prefix' => '',
                                'label' => $label,
                                'url_title' => '',
                                'intro' => '',
                                'material' => '',
                                'note' => ''
                            );
                        }

                    }
                }
            }

            if($ind2 === ' ') {
                if ($reference->cache["856[" . $i . "]"]->getSubfield('u')) {

                    $raw_url = trim($reference->cache["856[" . $i . "]"]->getSubfield('u')->getData());
                    $url = parse_url($raw_url);

                    $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.' . $url['host'];
                    $localisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) : '';      
    
                    $introLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.introlabel_links.no_relationship';
                    $introLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($introLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($introLocalisationKey) : '';      

                    if($localisedLabel === '') {
                        $note = $reference->cache["856[" . $i . "]"]->getSubfield('z') ? $reference->cache["856[" . $i . "]"]->getSubfield('z')->getData() : '';
                        $material = $reference->cache["856[" . $i . "]"]->getSubfield('3') ? $reference->cache["856[" . $i . "]"]->getSubfield('3')->getData(): '';
                        $general = $reference->cache["856[" . $i . "]"]->getSubfield('y') ? $reference->cache["856[" . $i . "]"]->getSubfield('y')->getData(): '';

                        if($note === 'lizenzpflichtig') {
                            $note = '';
                        }

                        if(str_ends_with($url['path'], '.zip')) {
                            $materialZipLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.material.zip';
                            $materialZipLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialZipLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialZipLocalisationKey) : '';      
        
                            $material = $materialZipLocalisedLabel;
                        }

                        $marclabel = $general;
                        if((strlen($marclabel) > 0) && (strlen($material) > 0)) {
                            $marclabel .= ' ; ';
                        }
                        $marclabel .= $material;
                        if((strlen($marclabel) > 0) && (strlen($note) > 0)) {
                            $marclabel .= ' ; ';
                        }
                        $marclabel .= $note;

                        if(str_contains($marclabel, '#')) {
                            $marclabel = str_replace('#', ' - ', $marclabel);
                        }

                        if(str_contains($marclabel, '#')) {
                            $marclabel = str_replace('#', ' - ', $marclabel);
                        }

                    }

                    $label = $introLocalisedLabel . ((strlen($localisedLabel) > 0) ? ' via ' : '') . $localisedLabel . ((strlen($marclabel) > 0) ? ' ('.$marclabel.')' : '');

                    $return_links['links'][] = array(
                        'url' => $raw_url,
                        'url_prefix' => '',
                        'label' => $label,
                        'url_title' => '',
                        'intro' => '',
                        'material' => '',
                        'note' => ''
                    );
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
                    'label' => $reference->cache["024_7"][$i]->getSubfield('a')->getData(),
                    'intro' => 'Verzeichnis der Drucke des 16. Jahrhunderts:',
                    'url_title' => '',
                    'material' => '',
                    'note' => ''
                );

            }
            if ($reference->cache["024_7"][$i]->getSubfield('2')->getData() == 'vd17') {

                if($reference->cache["024_7"][$i]->getSubfield('a')) {

                    $return_links['references'][] = array(
                        // Notiz:
                        // Links starten in der Katalogisierung mit VD17. Die Suche im Verzeichnis funktioniert nur ohne
                        'url' => 'https://kxp.k10plus.de/DB=1.28/CMD?ACT=SRCHA&IKT=8079&TRM=%27'.trim(ltrim($reference->cache["024_7"][$i]->getSubfield('a')->getData(), 'VD17')).'%27',
                        'url_prefix' => '',
                        'label' => $reference->cache["024_7"][$i]->getSubfield('a')->getData(),
                        'intro' => 'Verzeichnis der Drucke des 17. Jahrhunderts:',
                        'url_title' => '',
                        'material' => '',
                        'note' => ''
                    );

                }

            }
        }

        if (in_array("Sächsische Bibliografie", $document['mega_collection'])) {

            if($document['author_facet'][0]) {
                $label = htmlentities($document['author_facet'][0] .': '. $document['title_short']);
            } else {
                $label = htmlentities($document['title_short']);
            }



            $return_links['references'][] = array(
                'url' => 'https://swb.bsz-bw.de/DB=2.304/PPNSET?PPN='.$document['kxp_id_str'],
                'url_prefix' => '',
                'label' => substr($label, 0, 125).' (<f:image src="EXT:slub_katalog_beta/Resources/Public/Images/mega_collection/sxrm_icon.png" width="12" height="16" class="mega_collection_logo_inline"></f:image>Säbi)',
                'url_title' => $label,
                'intro' => 'Nachweis in der Sächsischen Bibliografie:',
                'material' => '',
                'note' => ''
            );
        }

        if(!$has_isil_links && !$is_marc) {

            foreach($document['url'] as $url) {
                if((strpos($url, '|')) && ($document['source_id'] != '215')) {
                    $url_parts = explode('|', $url);
                    $return_links['references'][] = array(
                        'url' => $url_parts[1],
                        'url_prefix' => '',
                        'label' => $url_parts[0],
                        'url_title' => '',
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
                            'url_title' => '',
                            'intro' => '',
                            'material' => '',
                            'note' => ''
                        );
                    } else {
                        $return_links['links'][] = array(
                            'url' => $url,
                            'url_prefix' => '',
                            'url_title' => '',
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

                    if($result->getFields()['author_facet'][0]) {
                        $label = htmlentities($result->getFields()['author_facet'][0] .': '. $result->getFields()['title_short']);
                    } else {
                        $label = htmlentities($result->getFields()['title_short']);
                    }


                    $localisedIntroI = '';
                    $localisedIntroN = '';
                    if($reference->cache[$selector][$i]->getSubfield('n') && $reference->cache[$selector][$i]->getSubfield('n')->getData()) {
                        $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.references.intro.marc.' . $reference->cache[$selector][$i]->getSubfield('n')->getData();
                        $localisedIntroN = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) : $reference->cache[$selector][$i]->getSubfield('n')->getData();     
                    }
                    if($reference->cache[$selector][$i]->getSubfield('i') && $reference->cache[$selector][$i]->getSubfield('i')->getData()) {
                        $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.references.intro.marc.' . $reference->cache[$selector][$i]->getSubfield('i')->getData();
                        $localisedIntroI = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) : $reference->cache[$selector][$i]->getSubfield('i')->getData();     
                    }

                    
                    if($reference->cache[$selector][$i]->getSubfield('i') && $reference->cache[$selector][$i]->getSubfield('i')->getData()) {

                        if($reference->cache[$selector][$i]->getSubfield('n') && $reference->cache[$selector][$i]->getSubfield('n')->getData()) {
                            $intro = $localisedIntroI . ' (' . $localisedIntroN . '):';
                        } else {
                            $intro = $localisedIntroI . ':';
                        }

                    } else {

                        $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.references.intro.marc.' . $selector;
                        $localisedIntro = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) : '';     

                        if($reference->cache[$selector][$i]->getSubfield('n') && $reference->cache[$selector][$i]->getSubfield('n')->getData()) {
                            $intro = $localisedIntro . " (". $localisedIntroN . "):";
                        } else {
                            $intro = $localisedIntro;
                        }

                    }

                    $return_links['references'][] = array(
                        'url' => '/id/'.$result->getFields()['id'],
                        'url_prefix' => '',
                        'label' => substr($label, 0, 125) . ' (<span class="reference_slub_logo">SLUB</span>)',
                        'url_title' => '',
                        'intro' => $intro,
                        'material' => '',
                        'note' => ''
                    );

                }
            }

        }

    }
}