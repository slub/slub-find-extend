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


/**
 * Slot implementation before the
 *
 * @category    Slots
 * @package     TYPO3
 */
class AdvancedQuery {

    /**
     * @var \Slub\SlubFindExtend\Services\StopWordService
     * @inject
     */
    protected $stopWordService;

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
     * Special handling for pure numeric queries
     *
     * @param $parameter
     */
    private function handleNumeric($parameter) {

        if(is_numeric($parameter)) {
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
    public function handlePhraseMatch($originalQuerystring, $searchHandler, $settings) {

        if(preg_match('/^".*"$/', trim($originalQuerystring))) { return ''; }

        $boost = ($settings['queryModifier']['phraseMatchBoost']) ? '^'.$settings['queryModifier']['phraseMatchBoost'] : '';

        return ' OR ' . $searchHandler->createAdvancedQueryString('"'.$originalQuerystring.'"') . $boost;

    }

    public function handleIsilMatch($originalQuerystring, $searchHandler, $settings) {

        if(preg_match('/^".*"$/', trim($originalQuerystring))) { return ''; }

        if(!$settings['queryModifier']['isilQueryString']) { return ''; }

        $originalQuerystring = trim($originalQuerystring, " \t\n\r\0\x0B*");
        $originalQuerystring = str_replace([' ', ')', '('], ['\\\ ', '\\\)', '\\\('], $originalQuerystring);
        
        $return = ' OR ' . sprintf($this->settings['queryModifier']['isilQueryString'], $originalQuerystring).'^500000';

        return $return;

    }

    /**
     * @param array $settings Settings Array
     */
    private function handleStripIntFields(&$settings, $queryParameter) {

        if(!is_numeric(substr($queryParameter, 0, 2))) {
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
    private function stripCharsFromQuery($queryParameter) {
            return str_replace(['/','\\'],[' '],$queryParameter);
    }



    /**
     * Slot to build the advanced query
     *
     * @param Query &$query
     * @param array $arguments request arguments
     */
    public function build(&$query, $arguments) {

        $originalQueryParameter = $queryParameter = is_array($arguments['q']['default']) ? $arguments['q']['default'][0] : $arguments['q']['default'];

        $settings = $this->settings['components'];

        if(strlen($queryParameter) > 0) {

            if($this->settings['queryModifier']) {

                if ( !$this->settings['queryModifier']['phraseMatch'] ) {
                      $queryParameter = $this->stripCharsFromQuery($queryParameter);
                }

                if($this->settings['queryModifier']['stopwords']) {
                    $queryParameter = $this->stopWordService->cleanQueryString($queryParameter);
                }

                if($this->settings['queryModifier']['numeric']) {
                    $queryParameter = $this->handleNumeric($queryParameter);
                }

            }

            if($this->settings['queryModifier'] && $this->settings['queryModifier']['stripIntFields']) {
                $this->handleStripIntFields($settings, $queryParameter);
            }

            $searchHandler = new SearchHandler($settings);

            $boostquery = $searchHandler->createBoostQueryString($queryParameter);

            $querystring = $searchHandler->createAdvancedQueryString($queryParameter);

            if($this->settings['queryModifier'] && $this->settings['queryModifier']['phraseMatch']) {
                $querystring .= $this->handlePhraseMatch($originalQueryParameter, $searchHandler, $this->settings);
            }

            if($this->settings['queryModifier'] && $this->settings['queryModifier']['isilMatch']) {
                $querystring .= $this->handleIsilMatch($originalQueryParameter, $searchHandler, $this->settings);
            }

            $query->setQuery($querystring);

        } else {

            if($settings['DismaxHandler'] === 'edismax') {
                $dismax = $query->getEDisMax();
            } else {
                $dismax = $query->getDisMax();
            }

            if($settings['DismaxParams']) {
                foreach ($settings['DismaxParams'] as $params) {
                    if ($params['name'] === 'bf') {
                        $dismax->setBoostFunctions($params['value']);
                    }
                    if ($params['name'] === 'bq') {
                        $dismax->setBoostQuery($params['value']);
                    }
                }
            }

        }

    }


}
