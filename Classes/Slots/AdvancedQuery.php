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
use Slub\SlubFindExtend\Backend\Solr\SearchHandler;
use Solarium\QueryType\Select\Query\Query;
use Slub\SlubFindExtend\Services\StopWordService;

/**
 * Slot implementation before the
 *
 * @category    Slots
 * @package     TYPO3
 */
class AdvancedQuery
{
    /**
     * @var Query
     */
    protected Query $query;

    /**
     * @var \Slub\SlubFindExtend\Services\StopWordService
     */
    protected $stopWordService = null;

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
     * @var \Solarium\Component\EdisMax
     */
    protected $dismax = null;

    /**
     * @param \Slub\SlubFindExtend\Services\StopWordService $stopWordService
     */
    public function __construct(\Slub\SlubFindExtend\Services\StopWordService $stopWordService)
    {
        $this->stopWordService = $stopWordService;
    }

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
     * @param \Slub\SlubFindExtend\Services\StopWordService $stopWordService
     * @return void
     */
    public function injectStopWordService(StopWordService $stopWordService){
        $this->stopWordService = $stopWordService;
    }

    /**
     * Special handling for pure numeric queries
     *
     * @param $parameter
     */
    private function handleNumeric($parameter)
    {
        if (is_numeric($parameter)) {
            $parameter = sprintf($this->settings['queryModifier']['numeric'], $parameter);
        }

        return $parameter;
    }

    /**
     * @param string $querystring
     * @param string $originalQuerystring
     * @param SearchHandler $searchHandler
     * @param array $settings
     * @return string
     */
    public function handlePhraseMatch($originalQuerystring, $searchHandler, $settings)
    {
        if (preg_match('/^".*"$/', trim($originalQuerystring))) {
            return '';
        }

        $boost = ($settings['queryModifier']['phraseMatchBoost']) ? '^'.$settings['queryModifier']['phraseMatchBoost'] : '';

        return ' OR ' . $searchHandler->createAdvancedQueryString('"'.$originalQuerystring.'"') . $boost;
    }

    public function handleIsilMatch($originalQuerystring, $searchHandler, $settings)
    {
        if (preg_match('/^".*"$/', trim($originalQuerystring))) {
            return '';
        }

        if (!$settings['queryModifier']['isilQueryString']) {
            return '';
        }

        $originalQuerystring = trim($originalQuerystring, $settings['queryModifier']['isilQueryTrim']);
        $originalQuerystring = str_replace([' ', ')', '('], ['\\\ ', '\\\)', '\\\('], $originalQuerystring);

        $return = ' OR ' . sprintf($this->settings['queryModifier']['isilQueryString'], $originalQuerystring);

        return $return;
    }

    /**
     * @param array $settings Settings Array
     */
    private function handleStripIntFields(&$settings, $queryParameter)
    {
        if (!is_numeric(substr($queryParameter, 0, 2))) {
            foreach ($settings['DismaxFields'] as $key => $value) {
                if (intval($key) >= 800) {
                    unset($settings['DismaxFields'][$key], $queryParameter);
                }
            }
        }
    }

    /**
     * strip chars that breaks the solr query
     *
     * @param string $queryParameter Settings Array
     */
    private function stripCharsFromQuery($queryParameter)
    {
        return str_replace(['/','\\'], [' '], $queryParameter);
    }

    /**
     * Clean Query parameters the solr query
     *
     * @param string $queryParameter Settings Array
     */
    private function cleanParameter($queryParameter)
    {
        return str_replace([':','?', ';', '-', '!', '&', '–', '(', ')', '+', '=', '$', '[', ']', '.', '„', '“', '‘', '’'], ' ', $queryParameter);
    }

    private function createQueryString(array $queryArray)
    private function createQueryString(array $queryArray): string
    {
        $allowedDismaxParmeters = ['qf','pf','pf2','pf3','ps','ps2','ps3','qs','tie','mm','bq','bf','sow'];
        $queryStringArray = ['default_query' => '*:*'];
        $filteredQueryFields = array_filter(
            $this->settings['queryFields'],
            static fn(array $entry): bool => in_array($entry['id'], array_keys($queryArray) ?? [])
        );
        if (empty($filteredQueryFields)) {
            return $queryStringArray['default_query'];
        }
        foreach ($filteredQueryFields as $fieldInfo) {
            $dismaxParmeters = [];
            $searchField = $fieldInfo['id'] ?? '';
            $searchTerms = is_array($queryArray[$fieldInfo['id']]) ? $queryArray[$fieldInfo['id']] : [$queryArray[$fieldInfo['id']]];
            $phrasedSearchTerms = [];
            // always escape search terms
            foreach (array_keys($searchTerms) as $key) {
                if (!preg_match('/^".*"$/', trim($searchTerms[$key]))) {
                    $searchTerms[$key] = empty($fieldInfo['phrase'])
                        ? $this->query->getHelper()->escapeTerm($searchTerms[$key])
                        : $this->query->getHelper()->escapePhrase($searchTerms[$key]);
                    // add additional phrased term if the term is not phrased already
                    if (!preg_match('/^".*"$/', trim($searchTerms[$key]))) {
                        $phrasedSearchTerms[] = $this->query->getHelper()->escapePhrase($searchTerms[$key]);
                    }
                } else {
                    // make sure that the phrased search term is escaped properly
                    $searchTerms[$key] = $this->query->getHelper()->escapePhrase(substr(trim($searchTerms[$key]), 1, -1));
                }
            }
            $searchTerms = array_merge($searchTerms, $phrasedSearchTerms);
            foreach ($fieldInfo as $k => $v) {
                switch($k) {
                    case in_array($k, $allowedDismaxParmeters):
                        $dismaxParmeters[$k] = $k . '=' . $this->query->getHelper()->escapeLocalParamValue($v);
                        break;
                    case 'operator':
                        $dismaxParmeters['q.op'] = 'q.op=' . $this->query->getHelper()->escapeLocalParamValue($v);
                        break;
                    case 'query':
                        preg_match('/^(?:(?<field>[^:]+):)?(?<query>.+)$/', $v, $queryParts);
                        $searchTerms = array_map(function ($term) use ($queryParts) {
                            return vsprintf($queryParts['query'], [$term]);
                        }, $searchTerms) ?? $searchTerms;
                        $searchField = $queryParts['field'] != '' ? $queryParts['field'] : $fieldInfo['id'];
                        break;
                }
            }
            // handle default Dismax parameters from TypoScript configuration
            if (is_array($this->settings['components']['DismaxParams'])) {
                foreach ($this->settings['components']['DismaxParams'] as $k => $v) {
                    if (in_array($k, $allowedDismaxParmeters) && !in_array($k, array_keys($dismaxParmeters))) {
                        $dismaxParmeters[$k] = $k . '=' . $this->query->getHelper()->escapeLocalParamValue($v);
                    }
                }
            }
            if(!preg_match('/^".*"$/', trim($searchTerm))) {
                $searchTerm = preg_replace('/\s*-\s+/', ' ', $searchTerm);
                // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($searchTerm, 'searchTerm after preg_replace');
            }
            */
            //$searchTerm = is_array($searchTerm) ? '"' . implode('" "', $searchTerm) . '"' : $searchTerm;
            $dismaxParmeters['v'] = 'v=' . $this->query->getHelper()->escapeLocalParamValue(implode(' ', $searchTerms));
            if ($searchField != '' && !in_array('qf', array_keys($dismaxParmeters)) && !in_array('pf', array_keys($dismaxParmeters))) {
                $dismaxParmeters['qf'] = 'qf=' . $this->query->getHelper()->escapeLocalParamValue($searchField);
                $dismaxParmeters['pf'] = 'pf=' . $this->query->getHelper()->escapeLocalParamValue($searchField . '^100');
            }
            $queryStringArray[$fieldInfo['id']] .= '{!edismax ' . implode(' ', $dismaxParmeters) . '}';
        }
        return implode(' ' . Query::QUERY_OPERATOR_AND . ' ', $queryStringArray);
    }

    /**
     * Slot to build the advanced query
     *
     * @param Query &$query
     * @param array $arguments request arguments
     */
    public function build(&$query, $arguments)
    {
        $this->query = $query;
        $queryParameter = trim(is_array($arguments['q']['default']) ? $arguments['q']['default'][0] : $arguments['q']['default']);
        $originalQueryParameter = $queryParameter;

        $settings = $this->settings['components'];

        if ($settings) {
            if (strlen($queryParameter) > 0) {
                if ($this->settings['queryModifier']) {
                    if (!$this->settings['queryModifier']['phraseMatch']) {
                        $queryParameter = $this->stripCharsFromQuery($queryParameter);
                    }

                    if ($this->settings['queryModifier']['cleanParameter']) {
                        $queryParameter = $this->cleanParameter($queryParameter);
                    }

                    if ($this->settings['queryModifier']['stopwords']) {
                        $queryParameter = $this->stopWordService->cleanQueryString($queryParameter);
                    }

                    if ($this->settings['queryModifier']['numeric']) {
                        $queryParameter = $this->handleNumeric($queryParameter);
                    }
                }

                if ($this->settings['queryModifier'] && $this->settings['queryModifier']['stripIntFields']) {
                    $this->handleStripIntFields($settings, $queryParameter);
                }

                $searchHandler = new SearchHandler($settings);

                $boostquery = $searchHandler->createBoostQueryString($queryParameter);

                $querystring = $searchHandler->createAdvancedQueryString($queryParameter);

                if ($this->settings['queryModifier'] && $this->settings['queryModifier']['phraseMatch']) {
                    $querystring .= $this->handlePhraseMatch($originalQueryParameter, $searchHandler, $this->settings);
                }

                if ($this->settings['queryModifier'] && $this->settings['queryModifier']['isilMatch']) {
                    $querystring .= $this->handleIsilMatch($originalQueryParameter, $searchHandler, $this->settings);
                }

                $this->query->setQuery($querystring);
            } else {
                $this->query->setQuery($this->createQueryString($arguments['q']));
            }
        }
    }
}
