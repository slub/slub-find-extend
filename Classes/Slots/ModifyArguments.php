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
use Slub\SlubFindExtend\Services\SessionHandlerService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use Solarium\QueryType\Select\Result\Document;


/**
 * Slot implementation before the
 *
 * @category    Slots
 * @package     TYPO3
 */
class ModifyArguments {

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
     *
     * @var SessionHandlerService
     */
    protected $sessionHandler;

    /**
     *
     * @param SessionHandlerService $sessionHandler
     */
    public function injectSessionHandler(SessionHandlerService $sessionHandler) {
        $this->sessionHandler = $sessionHandler;
    }

    /**
     * Slot to modify request arguments
     *
     * @param array &$assignments
     */
    public function modify(&$arguments) {
        if(strlen($arguments['id']) > 0) {
            if(is_array($arguments['underlyingQuery']) && (count($arguments['underlyingQuery']) > 0)) {
                $this->sessionHandler->writeToSession($arguments['underlyingQuery'], $arguments['id'].'_underlyingQuery');
            } else {
                $storedUnderlyingQuery = $this->sessionHandler->restoreFromSession($arguments['id'].'_underlyingQuery');
                if($storedUnderlyingQuery) {
                    $arguments['underlyingQuery'] = $storedUnderlyingQuery;
                }
            }
        }
    }

}
