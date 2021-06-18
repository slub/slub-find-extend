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
class HandleOneHit
{

    const IDFIELDS = ['record_id', 'barcode', 'rsn', 'isbn', 'ismn', 'issn', 'zdb', 'signatur', 'title', 'title_full', 'kxp_id_str', 'swb_id_str', 'finc_id_str'];

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
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
    }

    public function injectUriBuilder(UriBuilder $uriBuilder) {
        $this->uriBuilder = $uriBuilder;
    }
    /**
     * Slot to handle one hit results
     *
     * @param array &$resultSet
     */
    public function index(&$resultSet)
    {
        $idhit = false;
        if($this->settings['handleOnHit'] == "0")
            return;

        if (
            $resultSet
            && ($resultSet->getNumFound() === 1)
            && ((is_array($_GET['tx_find_find']['facet'])) && (count($_GET['tx_find_find']['facet']) === 0))
            && (!$_GET['type'] > 0)
        ) {

            /* @var $document Document */
            $document = $resultSet->getDocuments()[0];
            foreach ($resultSet->getHighlighting()->getResult($document['id'])->getFields() as $key => $value) {
                if(in_array($key, $this::IDFIELDS)) {
                    $idhit = true;
                }
            }

            if($idhit) {

                $uri = $this->uriBuilder->setUseCacheHash(0)->uriFor("detail", ['id' => $document['id'], 'underlyingQuery' => ['q' => $_GET['tx_find_find']['q'], 'position' => 1]], "Search", "find", "Find");

                header("Location: " . $uri, TRUE, 302);
                die();
            }

        }

    }

}
