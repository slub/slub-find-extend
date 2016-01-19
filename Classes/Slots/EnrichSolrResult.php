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
class EnrichSolrResult {

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
     * Slot to enrich finds detail view
     *
     * @param array &$assignments
     */
    public function detail(&$assignments) {

        $assignments['enriched'] = array('fields' => array());

        $document = $assignments['document'];
        /* @var $document Document */

        if($document) {

            $fields = $document->getFields();

            foreach ($this->settings['enrich']['detail'] as $enrichment) {

                $field_data = '';
                $user_data = '' | $GLOBALS['TSFE']->fe_user->user['username'];

                $check_fields = is_array($fields[$enrichment['check_field']]) ? $fields[$enrichment['check_field']] : array($fields[$enrichment['check_field']]);

                foreach ($check_fields as $check_field) {

                    if (preg_match($enrichment['check_pattern'], $check_field, $matches) === 1) {

                        $field_data = $matches[1];
                    }
                }

                if (strlen($field_data) > 0) {

                    // HTTP errors won't throw an exception
                    // TODO: Handle with Logging Service
                    $enriched = (array)$this->safe_json_decode(@file_get_contents(sprintf($enrichment['ws'], $field_data, $user_data)));

                    if(is_array($enriched) && count($enriched)) {
                        $assignments['enriched']['fields'] = array_merge($assignments['enriched']['fields'], $enriched);
                    }
                }
            }

        }

        $assignments['show_detaildata'] = $_COOKIE["show_detaildata"];

    }

    /**
     * Slot to enrich finds detail view
     *
     * @param array &$resultSet
     */
    public function index(&$resultSet) {

    }

    /**
     * A safe way to decode stringified json data
     * @param $value
     * @return mixed|string
     */
    private function safe_json_decode($value){

        $decoded = json_decode($value);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $decoded;
            case JSON_ERROR_UTF8:
                $clean = $this->unutf8ize($value);
                return $this->safe_json_decode($clean);
            default:
                return '';

        }
    }

    /**
     * Decode UTF8 recursively
     * @param $mixed
     * @return array|string
     */
    private function unutf8ize($mixed) {

        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->unutf8ize($value);
            }
        } else if (is_string ($mixed)) {
            return utf8_decode($mixed);
        }
        return $mixed;
    }


}
