<?php
namespace Slub\FindSlub\Slots;

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
use Slub\FindSlub\Backend\Solr\SearchHandler;
use Solarium\QueryType\Select\Query\Query;


/**
 * Slot implementation before the
 *
 * @category    Slots
 * @package     TYPO3
 */
class AdvancedQuery {

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
     * @param Query &$query
     * @param array $arguments request arguments
     */
    public function build(&$query, $arguments) {

        if(strlen($arguments['q']['default']) > 0) {

            $settings = $this->settings['components'];

            $searchHandler = new SearchHandler($settings);

            $boostquery = $searchHandler->createBoostQueryString($arguments['q']['default']);

            $querystring = $searchHandler->createAdvancedQueryString($arguments['q']['default']);

            $query->setQuery($querystring);

            /** @var \Solarium\QueryType\Select\Query\Component\EdisMax $edismax */
            $edismax = $query->getEDisMax();

        }

        // Needs to be dicussed if activated or not
        //$edismax->setBoostQuery($boostquery);

        //$edismax->setBoostFunctions("ord(publishDateSort)^10");

        //$edismax->setBoostQuery('mega_collection:"Qucosa"^10.0');

        //$edismax->setBoostQuery('(mega_collection:"Verbunddaten SWB")^100.0 OR (mega_collection:"SLUB/Deutsche Fotothek")^0.01');



    }


}
