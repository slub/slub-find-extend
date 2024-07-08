<?php

namespace Slub\SlubFindExtend\ViewHelpers\Link;

use Slub\SlubFindExtend\Services\MarcRefrenceResolverService;
use Slub\SlubFindExtend\Services\RediService;
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
     * @var RediService
     */
    protected static $rediService = null;

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('marc', 'string', 'The raw MARC', false, null);
        $this->registerArgument('document', 'array', 'The Solr doc', false, null);
        $this->registerArgument('enriched', 'array', 'The enriched data', false, null);

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

        $document = $arguments['document'];

        $isil_links = array();
        $isil_links = $arguments['document']['url_de14_str_mv'];
        $has_isil_links = false;
        if($isil_links && count($isil_links) > 0) {
            $has_isil_links = true;
        }
        $is_marc = false;
        if(($arguments['document']['recordtype'] === 'marc') || ($arguments['document']['recordtype'] === 'marcfinc'))
        {
            $is_marc = true;
        }

        if($has_isil_links) {
            foreach($isil_links as $isil_link) {

                $url = parse_url($isil_link);

                // Shitty special case for ezb and dbis
                if(str_contains($url['path'], 'ezeit')) {
                    $url['host'] = $url['host'].'/ezeit';
                }
                if(str_contains($url['path'], 'dbinfo')) {
                    $url['host'] = $url['host'].'/dbinfo';
                }
                if((str_contains($url['path'], 'ReadMe') && ($url['host'] === 'ezb.ur.de'))) {
                    if(str_contains($url['query'], 'lang=en')) {
                        $url['host'] = $url['host'].'/ReadMe/en';
                    }
                    if(str_contains($url['query'], 'lang=de')) {
                        $url['host'] = $url['host'].'/ReadMe/de';
                    }
                
                }

                $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.' . $url['host'];
                $localisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) : '';      

                $introLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.introlabel_access_format.' . $arguments['document']['format_de14'][0];
                $introLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($introLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($introLocalisationKey) : '';      

                $label = $introLocalisedLabel . ((strlen($localisedLabel) > 0) ? ' via ' : '') .$localisedLabel;              

                $note = '';
                $material = '';
                if(str_ends_with($isil_link, '.zip')) {
                    $materialZipLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.material.zip';
                    $materialZipLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialZipLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialZipLocalisationKey) : '';      

                    $material = $materialZipLocalisedLabel;
                }
                if(str_ends_with($isil_link, '.pdf')) {
                    $materialPdfLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.material.pdf';
                    $materialPdfLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialPdfLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialPdfLocalisationKey) : '';      

                    $material = $materialPdfLocalisedLabel;
                }

                if (str_ends_with($isil_link, 'manifest.json')) {

                    self::addLinkObjectToArray($return_links, 'access', array(
                        'url' => self::replaceDomains($isil_link, $document),
                        'url_prefix' => '',
                        'label' => $label,
                        'intro' => '',
                        'url_title' => '',
                        'material' => 'iiif',
                        'note' => ''
                    ));


                } else {

                    self::addLinkObjectToArray($return_links, 'access', array(
                        'url' => self::replaceDomains($isil_link, $document),
                        'url_prefix' => static::checkAndAddProxyPrefix($isil_link, $document, $note),
                        'label' => $label,
                        'intro' => '',
                        'url_title' => '',
                        'material' => $material,
                        'note' => ''
                    ));

                }

            }

            // Find iiif manifests      
            if($arguments['document'] && $arguments['document']['url']) {
                foreach($arguments['document']['url'] as $document_url) {
                    if (str_ends_with($document_url, 'manifest.json')) {

                        if(!in_array($document_url, $isil_links)) {


                            $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.iiif.arthistoricum';
                            $localisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) : '';   

                            $label = $introLocalisedLabel . ((strlen($localisedLabel) > 0) ? ' via ' : '') .$localisedLabel;

                            //$label = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.iiif.arthistoricum');

                            \TYPO3\CMS\Core\Utility\DebugUtility::debug($introLocalisedLabel);

                            self::addLinkObjectToArray($return_links, 'access', array(
                                'url' => 'https://iiif.arthistoricum.net/mirador/?id='.$document_url,
                                'url_prefix' => '',
                                'label' => $label,
                                'intro' => '',
                                'url_title' => '',
                                'material' => 'iiif',
                                'note' => ''
                            ));

                            self::addLinkObjectToArray($return_links, 'additional_information', array(
                                'url' => self::replaceDomains($document_url, $document),
                                'url_prefix' => '',
                                'label' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.iiif.manifest'),
                                'intro' => '',
                                'url_title' => '',
                                'material' => '',
                                'note' => ''
                            ));

                        }

                    }
                    
                }
            }

        }

        $marc = $arguments['marc'];
        $document = $arguments['document'];
        $enriched = $arguments['enriched'];

        if($is_marc) 
        {
            $decoder = new \Slub\SlubFindExtend\Slots\Decoder\Marc21();
            /** @var \File_MARC_Record */
            $decoded = $decoder->decode($marc);

            /** @var \Object */
            $reference = static::getMarcRefrenceResolverService()->resolveReference('856', $decoded);

            for ($i = 0; $i < count($reference->cache["856"]); $i++) {

                $ind1 = $reference->cache["856[" . $i . "]"]->getIndicator(1);
                $ind2 = $reference->cache["856[" . $i . "]"]->getIndicator(2);

                if(($ind2 === '0') || ($ind2 === '1')) {

                    if ($reference->cache["856[" . $i . "]"]->getSubfield('u')) {
                        $raw_url = trim($reference->cache["856[" . $i . "]"]->getSubfield('u')->getData());
                        $url = parse_url($raw_url);

                        // Shitty special case for ezb and dbis
                        if(str_contains($url['path'], 'ezeit')) {
                            $url['host'] = $url['host'].'/ezeit';
                        }
                        if(str_contains($url['path'], 'dbinfo')) {
                            $url['host'] = $url['host'].'/dbinfo';
                        }

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
                            if($value->getData() === 'lizenzpflichtig') {
                                break;
                            } else {
                                $note .= trim(ltrim($value->getData(), '// '));
                            }
                            ++$j;
                            if($j < count($reference->cache["856[" . $i . "]"]->getSubfields('z'))) {
                                $note .=  " ; ";
                            }
                        }

                        if(str_contains($note, 'kostenfrei')) {
                            $jsfunction = '$(document).ready(function() { showOAIcon(); });';
                        }

                        if(str_ends_with($url['path'], '.zip')) {
                            $materialZipLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.material.zip';
                            $materialZipLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialZipLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialZipLocalisationKey) : '';      
        
                            if((strlen($material) > 0) && (strlen($materialZipLocalisedLabel) > 0)) {
                                $material .=  " ; ";
                            }
                            $material .= $materialZipLocalisedLabel;
                        }
                        if(str_ends_with($url['path'], '.pdf')) {
                            $materialPdfLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.material.pdf';
                            $materialPdfLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialPdfLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialPdfLocalisationKey) : '';      
        
                            if((strlen($material) > 0) && (strlen($materialPdfLocalisedLabel) > 0)) {
                                $material .=  " ; ";
                            }
                            $material .= $materialPdfLocalisedLabel;
                        }

                        $marclabel = $general;

                        if($material !== $general) {
                            if((strlen($marclabel) > 0) && (strlen($material) > 0)) {
                                $marclabel .= ' ; ';
                            }
                            $marclabel .= $material;
                        }

                        if((strlen($marclabel) > 0) && (strlen($note) > 0)) {
                            $marclabel .= ' ; ';
                        }
                        $marclabel .= $note;

                        if(str_contains($marclabel, '#')) {
                            $marclabel = str_replace('#', ' - ', $marclabel);
                        }

                        if(str_contains($marclabel, 'teilw. kostenfrei')) {

                            $kostenfreiLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.kostenfrei.teilw';
                            $kostenfreiLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($kostenfreiLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($kostenfreiLocalisationKey) : '';

                            $marclabel = str_replace('teilw. kostenfrei', $kostenfreiLocalisedLabel, $marclabel);

                        }

                        if(str_contains($marclabel, 'kostenfrei')) {

                            $kostenfreiLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.kostenfrei';
                            $kostenfreiLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($kostenfreiLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($kostenfreiLocalisationKey) : '';

                            $marclabel = str_replace('kostenfrei', $kostenfreiLocalisedLabel, $marclabel);

                        }

                        $label = $introLocalisedLabel . ((strlen($localisedLabel) > 0) ? ' via ' : '') . $localisedLabel . ((strlen($marclabel) > 0) ? ' ('.$marclabel.')' : '');

                            // Notiz:
                            // Wenn der Link aus MARC schon in den links aus url_de14 
                            // vorhanden ist, dann wird er nicht zusätzlich hinzugefügt 
                            // Falls in $z Notizen enthalten sind werden die an den Zugangslink ergänzt.
                            $is_accessslink = false;
                            for($k = 0; $k < count($return_links['access']); $k++) {
                                if($raw_url === $return_links['access'][$k]['url']) {
                                    $is_accessslink = true;
                                    if(strlen($marclabel) > 0) {
                                        $return_links['access'][$k]['label'] .= ' (' . $marclabel . ')';
                                        $return_links['access'][$k]['jsfunction'] = $jsfunction;
                                    }
                                }
                            }

                            // Notiz:
                            // Wenn 856 40 und isilLinks vorhanden dann füge keine Links hinzu
                            if(($ind2 === '0') && $has_isil_links) {
                                $is_accessslink = true;
                            }

                            if(!$is_accessslink) {
                                // Notiz: 
                                // Wenn nicht source_id 0,füge hinzu zu den access links
                                // Wenn source_id 0 nur hinzufügen wenn note kostenfrei ist
                                if($document['source_id'] !== '0') {
                                    self::addLinkObjectToArray($return_links, 'access', array(
                                        'url' => self::replaceDomains($raw_url, $document),
                                        'url_prefix' => static::checkAndAddProxyPrefix($raw_url, $document, $note),
                                        'label' => $label,
                                        'url_title' => '',
                                        'intro' => '',
                                        'material' => '',
                                        'note' => '',
                                        'jsfunction' => $jsfunction
                                    ));
                                } else {
                                        if(str_contains($note, 'kostenfrei')) {
                                            self::addLinkObjectToArray($return_links, 'access', array(
                                                'url' => self::replaceDomains($raw_url, $document),
                                                'url_prefix' => static::checkAndAddProxyPrefix($raw_url, $document, $note),
                                                'label' => $label,
                                                'url_title' => '',
                                                'intro' => '',
                                                'material' => '',
                                                'note' => '',
                                                'jsfunction' => $jsfunction
                                            ));
                                        }
                                }
                                
                            }

                    }
            
                }

                if($ind2 === '2') {

                    if ($reference->cache["856[" . $i . "]"]->getSubfield('u')) {
                        $raw_url = trim($reference->cache["856[" . $i . "]"]->getSubfield('u')->getData());

                        if (!str_ends_with($raw_url, 'manifest.json')) {

                            $url = parse_url($raw_url);

                            $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.' . $url['host'];
                            $localisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) : '';      
    
                            $introLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.introlabel_additional_relationship.' . $ind2;
                            $introLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($introLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($introLocalisationKey) : '';      

                            $general = $reference->cache["856[" . $i . "]"]->getSubfield('y') ? $reference->cache["856[" . $i . "]"]->getSubfield('y')->getData(): '';
                            $material = $reference->cache["856[" . $i . "]"]->getSubfield('3') ? $reference->cache["856[" . $i . "]"]->getSubfield('3')->getData(): '';
                            $note = $reference->cache["856[" . $i . "]"]->getSubfield('z') ? $reference->cache["856[" . $i . "]"]->getSubfield('z')->getData() : '';

                            $note = '';
                            $j = 0;
                            foreach ($reference->cache["856[" . $i . "]"]->getSubfields('z') as $code => $value) {
                                // Notiz:
                                // In der Katalogisierung häufig verwendete Einleitung die für die Anzeige entfernt wird

                                if($value->getData() === 'lizenzpflichtig') {
                                    break;
                                } else {
                                    $note .= trim(ltrim($value->getData(), '// '));
                                }
                                ++$j;
                                if($j < count($reference->cache["856[" . $i . "]"]->getSubfields('z'))) {
                                    $note .=  " ; ";
                                }
                            }

                            if(str_ends_with($url['path'], '.zip')) {
                                $materialZipLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.material.zip';
                                $materialZipLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialZipLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialZipLocalisationKey) : '';      
            
                                if((strlen($material) > 0) && (strlen($materialZipLocalisedLabel) > 0)) {
                                    $material .=  " ; ";
                                }
                                $material .= $materialZipLocalisedLabel;
                            }
                            if(str_ends_with($url['path'], '.pdf')) {
                                $materialPdfLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.material.pdf';
                                $materialPdfLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialPdfLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialPdfLocalisationKey) : '';      
            
                                if((strlen($material) > 0) && (strlen($materialPdfLocalisedLabel) > 0)) {
                                    $material .=  " ; ";
                                }
                                $material .= $materialPdfLocalisedLabel;
                            }

                            $marclabel = $general;

                            if($material !== $general) {
                                if((strlen($marclabel) > 0) && (strlen($material) > 0)) {
                                    $marclabel .= ' ; ';
                                }
                                $marclabel .= $material;
                            }

                            if((strlen($marclabel) > 0) && (strlen($note) > 0)) {
                                $marclabel .= ' ; ';
                            }
                            $marclabel .= $note;

                            if(str_contains($marclabel, '#')) {
                                $marclabel = str_replace('#', ' - ', $marclabel);
                            }

                            if(str_contains($marclabel, ' // ')) {
                                $marclabel = str_replace(' // ', ' - ', $marclabel);
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
                                    if(strlen($marclabel) > 0) {
                                        $return_links['access'][$k]['label'] .= ' (' . $marclabel . ')';
                                    }
                                }
                            }

                            if(!$is_accessslink) {

                                self::addLinkObjectToArray($return_links, 'additional_information', array(
                                    'url' => self::replaceDomains($raw_url, $document),
                                    'url_prefix' => '',
                                    'label' => $label,
                                    'url_title' => '',
                                    'intro' => '',
                                    'material' => '',
                                    'note' => ''
                                ));

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

                        $note = $reference->cache["856[" . $i . "]"]->getSubfield('z') ? $reference->cache["856[" . $i . "]"]->getSubfield('z')->getData() : '';
                        $material = $reference->cache["856[" . $i . "]"]->getSubfield('3') ? $reference->cache["856[" . $i . "]"]->getSubfield('3')->getData(): '';
                        $general = $reference->cache["856[" . $i . "]"]->getSubfield('y') ? $reference->cache["856[" . $i . "]"]->getSubfield('y')->getData(): '';

                        $note = '';
                        $j = 0;
                        foreach ($reference->cache["856[" . $i . "]"]->getSubfields('z') as $code => $value) {
                            // Notiz:
                            // In der Katalogisierung häufig verwendete Einleitung die für die Anzeige entfernt wird

                            if($value->getData() === 'lizenzpflichtig') {
                                break;
                            } else {
                                $note .= trim(ltrim($value->getData(), '// '));
                            }
                            ++$j;
                            if($j < count($reference->cache["856[" . $i . "]"]->getSubfields('z'))) {
                                $note .=  " ; ";
                            }
                        }

                        if($note === 'kostenfrei') {
                            $jsfunction = '$(document).ready(function() { showOAIcon(); });';
                        }

                        if(str_ends_with($url['path'], '.zip')) {
                            $materialZipLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.material.zip';
                            $materialZipLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialZipLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialZipLocalisationKey) : '';      
        
                            if((strlen($material) > 0) && (strlen($materialZipLocalisedLabel) > 0)) {
                                $material .=  " ; ";
                            }
                            $material .= $materialZipLocalisedLabel;
                        }
                        if(str_ends_with($url['path'], '.pdf')) {
                            $materialPdfLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.material.pdf';
                            $materialPdfLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialPdfLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($materialPdfLocalisationKey) : '';      
        
                            if((strlen($material) > 0) && (strlen($materialPdfLocalisedLabel) > 0)) {
                                $material .=  " ; ";
                            }
                            $material .= $materialPdfLocalisedLabel;
                        }

                        $marclabel = $general;

                        if($material !== $general) {
                            if((strlen($marclabel) > 0) && (strlen($material) > 0)) {
                                $marclabel .= ' ; ';
                            }
                            $marclabel .= $material;
                        }

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

                        // Notiz:
                        // Links ohne intro label wenn marclabel vorhanden
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

                        //$label = $introLocalisedLabel . ((strlen($localisedLabel) > 0) ? ' via ' : '') . $localisedLabel . ((strlen($marclabel) > 0) ? ' ('.$marclabel.')' : '');

                        self::addLinkObjectToArray($return_links, 'links', array(
                            'url' => self::replaceDomains($raw_url, $document),
                            'url_prefix' => '',
                            'label' => $label,
                            'url_title' => '',
                            'intro' => '',
                            'material' => '',
                            'note' => '',
                            'jsfunction' => $jsfunction
                        ));

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

                    self::addLinkObjectToArray($return_links, 'references', array(
                        'url' => 'https://gateway-bayern.de/'.urlencode($reference->cache["024_7"][$i]->getSubfield('a')->getData()),
                        'url_prefix' => '',
                        'label' => $reference->cache["024_7"][$i]->getSubfield('a')->getData(),
                        'intro' => 'Verzeichnis der Drucke des 16. Jahrhunderts:',
                        'url_title' => '',
                        'material' => '',
                        'note' => ''
                    ));

                }
                if ($reference->cache["024_7"][$i]->getSubfield('2')->getData() == 'vd17') {

                    if($reference->cache["024_7"][$i]->getSubfield('a')) {

                        self::addLinkObjectToArray($return_links, 'references', array(
                            // Notiz:
                            // Links starten in der Katalogisierung mit VD17. Die Suche im Verzeichnis funktioniert nur ohne
                            'url' => 'https://kxp.k10plus.de/DB=1.28/CMD?ACT=SRCHA&IKT=8079&TRM=%27'.trim(ltrim($reference->cache["024_7"][$i]->getSubfield('a')->getData(), 'VD17')).'%27',
                            'url_prefix' => '',
                            'label' => $reference->cache["024_7"][$i]->getSubfield('a')->getData(),
                            'intro' => 'Verzeichnis der Drucke des 17. Jahrhunderts:',
                            'url_title' => '',
                            'material' => '',
                            'note' => ''
                        ));

                    }

                }
            }

        }

        if (in_array("Sächsische Bibliografie", $document['mega_collection'])) {

            if($document['author_facet'][0]) {
                $label = htmlentities($document['author_facet'][0] .': '. $document['title_short']);
            } else {
                $label = htmlentities($document['title_short']);
            }

            if(strlen($label) >= 150 ) {
                $label = array_shift(explode("\n", wordwrap($label, 150))) . '...';
            }

            self::addLinkObjectToArray($return_links, 'references', array(
                'url' => 'https://swb.bsz-bw.de/DB=2.304/PPNSET?PPN='.$document['kxp_id_str'],
                'url_prefix' => '',
                'label' => $label.' (<img src="/typo3conf/ext/slub_katalog_beta/Resources/Public/Images/mega_collection/sxrm_icon.png" width="12" height="16" class="mega_collection_logo_inline" />Säbi)',
                'url_title' => $label,
                'intro' => 'Nachweis in der Sächsischen Bibliografie:',
                'material' => '',
                'note' => ''
            ));

        }
        

        if(!$has_isil_links && !$is_marc && $document && $document['url']) {

            foreach($document['url'] as $raw_url) {                

                if(($document['source_id'] != '215')) {

                    if(strpos($raw_url, '|')) {
                        
                        $url_parts = explode('|', $raw_url);

                        self::addLinkObjectToArray($return_links, 'references', array(
                            'url' => self::replaceDomains($url_parts[1], $document),
                            'url_prefix' => '',
                            'label' => $url_parts[0],
                            'url_title' => '',
                            'intro' => '',
                            'material' => '',
                            'note' => ''
                        ));

                    } else {

                        $url = parse_url($raw_url);

                        $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.' . $url['host'];
                        $localisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) : '';      

                        $introLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.introlabel_access_format.' . $arguments['document']['format_de14'][0];
                        $introLocalisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($introLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($introLocalisationKey) : '';      
        
                        if(($document['recordtype'] === 'ai')) {

                            $redi = static::getRediService()->getCached($document, $enriched);

                            $linknote = '';
                            if($redi['status'] === 2) 
                            {
                                $linknoteLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.status_redi.2';
                                $linknote = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($linknoteLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($linknoteLocalisationKey) : '';      
        
                            }

                            if($redi['oa_url']) 
                            {

                                $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.' . $redi['oa_via'];
                                $localisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) : $redi['oa_via']; 

                                self::addLinkObjectToArray($return_links, 'access', array(
                                    'url' => $redi['oa_url'],
                                    'url_prefix' => '',
                                    'label' => $introLocalisedLabel . ((strlen($localisedLabel) > 0) ? ' via ' : '') .$localisedLabel . ' ('. ( $redi['oa_more'] ? $redi['oa_more'] . ', ' : '' )  .'gefunden durch Unpaywall)',
                                    'url_title' => '',
                                    'intro' => '',
                                    'material' => '',
                                    'note' => '',
                                    'jsfunction' => '$(document).ready(function() { showOAIcon(); });'
                                ));

                            } else {


                                if($redi['doilink']) {

                                    self::addLinkObjectToArray($return_links, 'access', array(
                                        'url' => self::replaceDomains($raw_url, $document),
                                        'url_prefix' => static::checkAndAddProxyPrefix($raw_url, $document, $note),
                                        'label' => $introLocalisedLabel . ((strlen($localisedLabel) > 0) ? ' via ' : '') .$localisedLabel,
                                        'url_title' => '',
                                        'intro' => '',
                                        'material' => '',
                                        'note' => $linknote
                                    ));
    
                                } else {

                                    $finalUrl = static::checkRedirectTargetCached($redi['url']);
                                    $url = parse_url($finalUrl);

                                    $localisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.target.' . $url['host'];
                                    $localisedLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($localisationKey) : $redi['via'];     

                                    self::addLinkObjectToArray($return_links, 'access', array(
                                        'url' => $finalUrl,
                                        'url_prefix' => static::checkAndAddProxyPrefix($finalUrl, $document, $note),
                                        'label' => $introLocalisedLabel . ((strlen($localisedLabel) > 0) ? ' via ' : '') .$localisedLabel,
                                        'url_title' => '',
                                        'intro' => '',
                                        'material' => '',
                                        'note' => $linknote
                                    ));
                                    
                                }
    
                                if($redi['infolink']) {
    
                                    self::addLinkObjectToArray($return_links, 'access', array(
                                        'url' => $redi['infolink'],
                                        'url_prefix' => '',
                                        'label' => 'Zugangsbedingungen via EZB',
                                        'url_title' => '',
                                        'intro' => '',
                                        'material' => '',
                                        'note' => ''
                                    ));

                                }

                            }

                        } else {

                            // Sonderfall DIN / VDE
                            // Wenn Document in title_short DIN & VDE enthaölt udn source_id 211 ist,
                            // enthält dann ergänzen wir $note mit Hinweis auf DIN VDE Normen

                            if(($document['source_id'] === '211') && (strpos($document['title_short'], 'DIN') !== false) && (strpos($document['title_short'], 'VDE') !== false)) {

                                $nautosNoteLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.nautos-note';
                                $nautosNoteLocalisationLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($nautosNoteLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($nautosNoteLocalisationKey) : '';     

                            }

                            $label = $introLocalisedLabel . ((strlen($localisedLabel) > 0) ? ' via ' : '') . $localisedLabel . ((strlen($nautosNoteLocalisationLabel) > 0) ? ' ('.$nautosNoteLocalisationLabel.')' : '');

                            self::addLinkObjectToArray($return_links, 'links', array(
                                'url' => self::replaceDomains($raw_url, $document),
                                'url_prefix' => '',
                                'label' =>  $label,
                                'url_title' => '',
                                'intro' =>  '',
                                'material' => '',
                                'note' => ''
                            ));


                            // Sonderfall DIN / VDE
                            // Wenn Document in title_short DIN & VDE enthaölt udn source_id 211 ist,
                            // enthält dann ergänzen wir $note mit Hinweis auf DIN VDE Normen

                            if(($document['source_id'] === '211') && (strpos($document['title_short'], 'DIN') !== false) (strpos($document['title_short'], 'VDE') !== false)) {

                                $nautos3dLocalisationKey = 'LLL:' . $templateVariableContainer->get('settings')['languageRootPath'] . 'locallang.xml:links.nautos-note.3d';
                                $nautos3dLocalisationLabel = (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($nautos3dLocalisationKey) !== NULL) ? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($nautos3dLocalisationKey) : '';     


                                self::addLinkObjectToArray($return_links, 'links', array(
                                    'url' => 'https://3d.slub-dresden.de/viewer?p=3&b=5&f=11&l=6755&lang=de&search_text=vde',
                                    'url_prefix' => '',
                                    'label' => $nautos3dLocalisationLabel,
                                    'url_title' => '',
                                    'intro' => '',
                                    'material' => '',
                                    'note' => ''
                                ));

                            }

                        }


                    }

                    
                } else {

                    if(strpos($raw_url, '|')) {
                        $url_parts = explode('|', $raw_url);

                        self::addLinkObjectToArray($return_links, 'links', array(
                            'url' => self::replaceDomains($url_parts[1], $document),
                            'url_prefix' => '',
                            'label' => $url_parts[0],
                            'url_title' => '',
                            'intro' => '',
                            'material' => '',
                            'note' => ''
                        ));

                    } else {

                        self::addLinkObjectToArray($return_links, 'links', array(
                            'url' => self::replaceDomains($raw_url, $document),
                            'url_prefix' => '',
                            'url_title' => '',
                            'label' => '',
                            'intro' => '',
                            'material' => '',
                            'note' => ''
                        ));

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

    private static function getRediService()
    {
        if (null === static::$rediService) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            static::$rediService = $objectManager->get(RediService::class);
        }

        return static::$rediService;
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

                $id = str_replace('(DE-627)', '', $reference->cache[$selector][$i]->getSubfield('w')->getData());

                $query = static::createQuery($solrClient, 'kxp_id_str:"'.$id.'"', $templateVariableContainer);
                $solrClient->setOptions(static::getSolariumClientOptionsArray($templateVariableContainer, $query));

                /** @var Result $resultSet */
                $resultSet = static::$solr->select($query);

                /** @var DocumentInterface $result */
                $results = $resultSet->getDocuments();

                if ($results) {
                    /** @var \Solarium\QueryType\Select\Result\Document */
                    $result = $results[0];

                    if($reference->cache[$selector][$i]->getSubfield('a') && $reference->cache[$selector][$i]->getSubfield('a')->getData()) {
                        $label = htmlentities(preg_replace("/,\s?\d{0,4}\s-\s\d{0,4}/", "", $reference->cache[$selector][$i]->getSubfield('a')->getData()) .': '. $reference->cache[$selector][$i]->getSubfield('t')->getData());
                    } else {
                        $label = htmlentities($reference->cache[$selector][$i]->getSubfield('t')->getData());
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
                            $intro = $localisedIntro .":";
                        }

                    }

                    if(strlen($label) >= 150 ) {
                        $label = array_shift(explode("\n", wordwrap($label, 150))) . '...';
                    }

                    self::addLinkObjectToArray($return_links, 'references', array(
                        'url' => '/id/'.$result->getFields()['id'],
                        'url_prefix' => '',
                        'label' => $label . ' (<span class="reference_slub_logo">SLUB</span>)',
                        'url_title' => '',
                        'intro' => $intro,
                        'material' => '',
                        'note' => ''
                    ));

                }
            }

        }

    }

    /** 
     * Check URL and add prefix wehen needed
     * 
     * @param string $url
     * @param array $document
     */
    private static function checkAndAddProxyPrefix($url, $document, $note) 
    {

        $proxy_prefix = 'https://wwwdb.dbod.de/login?url=';  
        $return_prefix = $proxy_prefix;
        $no_prefix_hosts = ['dbis.uni-regensburg.de', 'www.bibliothek.uni-regensburg.de','ezb.ur.de', 'wwwdb.dbod.de', 'www.dbod.de', 'nbn-resolving.de', 'digital.slub-dresden.de', 'digital.zlb.de', 'www.deutschefotothek.de', 'mediathek.slub-dresden.de', 'rzblx10.uni-regensburg.de'];
        $force_prefix_hosts = [];

        $urlParsed = parse_url($url);

        if (in_array($urlParsed['host'], $no_prefix_hosts)) {
            $return_prefix =  '';
        }

        if (in_array('Free', $document['facet_avail'])) {
            $return_prefix =  '';
        }

        if ($document['access_state_str'] === 'Open Access') {
            $return_prefix =  '';
        }

        if ($note === 'kostenfrei') {
            $return_prefix =  '';
        }

        if (in_array($urlParsed['host'], $force_prefix_hosts)) {
            $return_prefix =  $proxy_prefix;
        }

        return $return_prefix;
    }

    private static function checkRedirectTargetCached($url)
    {
        $cache = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('resolv_link_electronic');
        $cacheIdentifier = sha1($url);
        $entry = $cache->get($cacheIdentifier);
        if (!$entry) {
            // Try to resolve article against redi
            $entry = static::checkRedirectTarget($url);
            $cache->set($cacheIdentifier, $entry);
        }

        return $entry;
    }

    /**
     * Check if URL is a redirect and return the final URL
     * 
     * @param string $url
     */
    private static function checkRedirectTarget($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        
        $a = curl_exec($ch); 
        
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); 
        
        return $finalUrl;

    }

    /**
     * replaceDomains
     * 
     * @param string $url
     * @param array $document
     */
    private static function replaceDomains($url, $document)
    {
        $url = str_replace('http://deposit.d-nb.de', 'https://deposit.dnb.de', $url);

        if(str_contains($url, 'opus.kobv.de')) {
            foreach($document['url'] as $document_url) {
                if(str_contains($document_url, 'nbn-resolving.de')) {
                    $url = $document_url;
                }
            }
        }
        
        return $url;

    }

    /**
     * validateLinksArray
     * 
     * Check if the links array is valid
     * 
     * @param array|boolean $linksArray
     */
    private static function validateLinksArray($linksArray)
    {
        if(strlen($linksArray['url']) === 0) {
            return FALSE;
        }

        return $linksArray;;
    }

    private static function addLinkObjectToArray(&$linksArray, $type, $linkObject)
    {
        $isValidObject = self::validateLinksArray($linkObject);

        if($isValidObject) {
            $linksArray[$type][] = $linkObject;
        }
    }

}