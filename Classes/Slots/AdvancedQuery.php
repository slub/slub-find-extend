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


    /**
     * Handle Dismax parameters for the solr query
     *
     * @param array $dismaxParameters Settings Array
     */
    private function handleDismaxParameters($dismaxParameters)
    {
        if($this->dismax instanceof \Solarium\Component\EdisMax) {
            foreach($dismaxParameters as $key => $value) {
                if (isset($value) && $value !== '') {
                    switch ($key) {
                        case 'bf':
                            $this->dismax->setBoostFunctions($value);
                            break;
                        case 'boost':
                            $this->dismax->setBoostFunctionsMult($value);
                            break;
                        case 'bq':
                            if(is_array($value)) {
                                $this->dismax->setBoostQueries($value);
                            } else {
                                $this->dismax->setBoostQuery($value);
                            }
                            break;
                        case 'fq':
                            $additionalFilters = is_array($value) ? $value : [$value];
                            $query->clearFilterQueries();
                            foreach ($additionalFilters as $key => $filterQuery) {
                                $query->createFilterQuery('additionalFilter-'.$key)->setQuery($filterQuery);
                            }
                            break;
                        case 'fq+':
                            #TODO: Implementation
                            break;
                        case 'mm':
                            $this->dismax->setMinimumMatch($value);
                            break;
                        case 'pf':
                            $this->dismax->setPhraseFields($value);
                            break;
                        case 'pf2':
                            $this->dismax->setPhraseBigramFields($value);
                            break;
                        case 'pf3':
                            $this->dismax->setPhraseTrigramFields($value);
                            break;
                        case 'ps':
                            $this->dismax->setPhraseSlop($value);
                            break;
                        case 'ps2':
                            $this->dismax->setPhraseBigramSlop($value);
                            break;
                        case 'ps3':
                            $this->dismax->setPhraseTrigramSlop($value);
                            break;
                        case 'qf':
                            $this->dismax->setQueryFields($value);
                            break;
                        case 'q.alt':
                            $this->dismax->setQueryAlternative($value);
                            break;
                        case 'qs':
                            $this->dismax->setQueryPhraseSlop($value);
                            break;
                        case 'tie':
                            $this->dismax->setTie($value);
                            break;
                        case 'uf':
                            $this->dismax->setUserFields($value);
                            break;
                        case 'sow':
                            //TODO: Check $value for boolean
                            //$query->setSplitOnWhitespace($value);
                            break;
                    }
                }
            }
        }
    }

    /**
     * Slot to build the advanced query
     *
     * @param Query &$query
     * @param array $arguments request arguments
     */
    public function build(&$query, $arguments)
    {
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

                $query->setQuery($querystring);
            } else {
                $this->dismax = $query->getEDisMax();

                if (is_array($settings['DismaxParams'])) {
                    $this->handleDismaxParameters($settings['DismaxParams']);
                }

                if (is_array($this->settings['queryFields'])) {
                    foreach ($this->settings['queryFields'] as $queryField) {
                        if (array_key_exists('id', $queryField ?? []) && array_key_exists($queryField['id'], $arguments['q'] ?? [])) {
                            $this->handleDismaxParameters($queryField);
                        }
                    }
                }
            }
        }
    }
}
