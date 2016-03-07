<?php
namespace Slub\SlubFindExtend\Slots;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use Solarium\QueryType\Select\Result\Document;


/**
 * Slot implementation before the
 *
 * @category    Slots
 * @package     TYPO3
 */
class ModifySolrResult {

    /**
     * Contains the settings of the current extension
     *
     * @var array
     * @api
     */
    protected $settings;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
    }

    /**
     * Slot to decode data from Solr result to use in templates
     *
     * @param array &$assignments
     */
    public function decode(&$assignments) {

        $document = $assignments['document'];
        /* @var $document Document */

        if($document && $this->settings['decode']) {

            $fields = $document->getFields();

            foreach ($this->settings['decode'] as $decoding) {

                if(($decoding['type'] === 'marc21')
                    && (strrpos($fields[$decoding['field']], 'blob:', -strlen($fields[$decoding['field']])) === FALSE)) {

                    $decoder = new \Slub\SlubFindExtend\Slots\Decoder\Marc21();
                    $assignments['decoded'][$decoding['field']] = $decoder->decode($fields[$decoding['field']]);

                }

            }

        }
    }

    /**
     * Slot to filter data from Solr result against blacklist values
     *
     * @param array &$assignments
     */
    public function blacklist(&$assignments) {
        
        $document = $assignments['document'];
        
        if($document && $this->settings['blacklist']) {
        
            $fields = $document->getFields();
        
            foreach ($this->settings['blacklist'] as $blacklistName => $blacklistValues) {
        
                if(isset($fields[$blacklistName]) && is_array($fields[$blacklistName]) && is_array($blacklistValues)) {
        
                    $fields[$blacklistName] = array_values(array_diff($fields[$blacklistName], $blacklistValues));
        
                }
        
            }

            $assignments['document'] = new \Solarium\QueryType\Select\Result\Document($fields);
        }
    }

    /**
     * Slot to enrich finds detail view
     *
     * @param array &$resultSet
     */
    public function index(&$resultSet) {

    }


}
