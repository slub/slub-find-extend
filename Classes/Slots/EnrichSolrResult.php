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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use Solarium\QueryType\Select\Result\Document;

/**
 * Slot implementation before the
 *
 * @category    Slots
 * @package     TYPO3
 */
class EnrichSolrResult implements \Psr\Log\LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct()
    {
        $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger(__CLASS__);
    }

    /**
     * Contains the settings of the current extension
     *
     * @var array
     * @api
     */
    protected $settings;

    /**
     * Contains data to be logged on error
     *
     * @var string
     * @api
     */
    protected $logData;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
    }

    /**
     * Slot to enrich finds detail view
     *
     * @param array &$assignments
     */
    public function detail(&$assignments)
    {
        $assignments['enriched'] = array('fields' => array());

        $document = $assignments['document'];
        /* @var $document Document */

        if ($document) {
            $fields = $document->getFields();
            $pageType = (int)\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('type');

            if ($this->settings['enrich'] && $this->settings['enrich']['detail']) {
                foreach ($this->settings['enrich']['detail'] as $enrichment) {
                    $filter_passed = false;

                    if (isset($enrichment['filter_field']) && isset($enrichment['filter_pattern'])) {
                        $filter_fields = is_array($fields[$enrichment['filter_field']]) ? $fields[$enrichment['filter_field']] : array($fields[$enrichment['filter_field']]);

                        foreach ($filter_fields as $filter_field) {
                            if (preg_match($enrichment['filter_pattern'], $filter_field,) === 1) {
                                $filter_passed = true;
                                break;
                            }
                        }
                    } else {
                        $filter_passed = true;
                    }

                    if ($filter_passed) {
                        $field_data = '';
                        $user_data = ($GLOBALS['TSFE']->fe_user->user['username']) ? $GLOBALS['TSFE']->fe_user->user['username'] : '';

                        $check_fields = is_array($fields[$enrichment['check_field']]) ? $fields[$enrichment['check_field']] : array($fields[$enrichment['check_field']]);

                        $check_typenum = false;

                        if (array_key_exists('check_typenum', $enrichment) && $pageType !== (int)$enrichment['check_typenum']) {
                            $check_typenum = true;
                        }

                        foreach ($check_fields as $check_field) {
                            if (preg_match($enrichment['check_pattern'], $check_field, $matches) === 1) {
                                $field_data = $matches[1];
                            }
                        }

                        if (strlen($field_data) > 0 && !$check_typenum) {

                            // HTTP errors won't throw an exception
                            // TODO: Handle with Logging Service
                            $this->logData = $fields['id'] . ': ' . sprintf($enrichment['ws'], $field_data, $user_data);
                            $enriched = (array)$this->safe_json_decode($this->getData(sprintf($enrichment['ws'], $field_data, $user_data)));

                            if (is_array($enriched) && count($enriched)) {
                                $assignments['enriched']['fields'] = array_merge($assignments['enriched']['fields'], $enriched);

                                foreach ($assignments['enriched']['fields'] as $key => $value) {
                                    if ($key != str_replace(' ', '', $key)) {
                                        $assignments['enriched']['fields'][str_replace(' ', '', $key)] = $assignments['enriched']['fields'][$key];
                                    }
                                }
                            }
                        }
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
    public function index(&$resultSet)
    {
    }

    /**
     * A safe way to decode stringified json data
     * @param $value
     * @return mixed|string
     */
    private function safe_json_decode($value)
    {
        $original_value = $value;

        $decoded = json_decode($value, true);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $decoded;
            case JSON_ERROR_UTF8:
                $this->logger->info('JSON_ERROR_UTF8: '. $this->logData);
                $clean = $this->unutf8ize($value);
                return $this->safe_json_decode($clean);
            case JSON_ERROR_SYNTAX:
                $this->logger->info('JSON_ERROR_SYNTAX: '. $this->logData);
                // Fix double ,, syntax error
                if (strpos($original_value, ',,') !== false) {
                    $value = str_replace(',,', ',', $original_value);
                    $decoded = json_decode($value, true);
                    return $decoded;
                }
                return '';
            case JSON_ERROR_CTRL_CHAR:
                $this->logger->info('JSON_ERROR_CTRL_CHAR: '. $this->logData);
                // Fix tab syntax error
                $fixed = 0;
                if (strpos($original_value, "\t") !== false) {
                    $value = str_replace("\t", '', $original_value);
                    $decoded = json_decode($value, true);
                    $fixed = 1;                }
                if (strpos($original_value, "\n") !== false) {
                    $value = str_replace("\n", '', $value);
                    $decoded = json_decode($value, true);
                    $fixed = 1;
                }
                if (strpos($original_value, "\r") !== false) {
                    $value = str_replace("\r", '', $value);
                    $decoded = json_decode($value, true);
                    $fixed = 1;
                }
                if($fixed === 1) {
                    return $decoded;
                } else {
                    return '';
                }
            default:
                return '';

        }
    }

    /**
     * Decode UTF8 recursively
     * @param $mixed
     * @return array|string
     */
    private function unutf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->unutf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return utf8_decode($mixed);
        }
        return $mixed;
    }

    private function getData($url)
    {
        $ch = curl_init();
        $timeout = 10;
        if ($this->settings['enrich'] && $this->settings['enrich']['timeout']) {
            if(intval($this->settings['enrich']['timeout']) > 0) {
                $timeout = intval($this->settings['enrich']['timeout']);
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
