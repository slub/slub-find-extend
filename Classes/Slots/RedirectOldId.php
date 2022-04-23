<?php

namespace Slub\SlubFindExtend\Slots;

use Solarium\QueryType\Select\Result\Document;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

/**
 * Slot implementation before the
 *
 * @category    Slots
 * @package     TYPO3
 */
class RedirectOldId
{
     /**
     * Contains the settings of the current extension
     *
     * @var array
     * @api
     */
    protected $settings;

    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

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

    public function injectUriBuilder(UriBuilder $uriBuilder)
    {
        $this->uriBuilder = $uriBuilder;
    }
    /**
     * Slot to redirect from old id to new id
     *
     * @param array &$result
     */
    public function redirect(&$result)
    {
        if ($this->settings['redirectOldId'] && $this->settings['redirectOldId']['active'] == 1) {
            if ($result['document'] && ($result['document']->getFields()['id'] !== $result['document']->getFields()[$this->settings['redirectOldId']['oldField']])
                && ($_GET['tx_find_find']['id']) === $result['document']->getFields()[$this->settings['redirectOldId']['oldField']]) {
                $uri = $this->uriBuilder->setUseCacheHash(0)->uriFor("detail", ['id' => $result['document']->getFields()['id']], "Search", "find", "Find");

                header("Location: " . $uri, true, 301);
                die();
            }
        }
    }
}
