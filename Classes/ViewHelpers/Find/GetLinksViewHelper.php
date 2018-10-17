<?php

namespace Slub\SlubFindExtend\ViewHelpers\Find;


use Slub\SlubFindExtend\Services\HoldingStatusService;

class GetLinksViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * @var \Slub\SlubFindExtend\Services\LinksFromMarcFullrecordService
     * @inject
     */
    protected $linksFromMarcFullrecordService;

    /**
     * @var \Slub\SlubFindExtend\Services\LinksFromAiFullrecordService
     * @inject
     */
    protected $linksFromAiFullrecordService;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('document', 'object', 'The index document', TRUE);
        $this->registerArgument('fullrecord', 'object', 'The raw fullrecord ', FALSE, NULL);
        $this->registerArgument('isil', 'array', 'If you want to filter the data by isil', FALSE, NULL);
        $this->registerArgument('index', 'boolean', 'Is this a call from an index overview?', FALSE, FALSE);
        $this->registerArgument('unique', 'boolean', 'Should only unique Links be outputted?', FALSE, FALSE);
    }

    /**
     * @return array
     */
    public function render()
    {

        if (($this->arguments['document']['recordtype'] === 'ai') && (!$this->arguments['index'])) {

            return $this->linksFromAiFullrecordService->getLinks($this->arguments['fullrecord'], $this->arguments['isil'], true);

        } else {

            switch ($this->arguments['document']['recordtype']) {
                case 'marc':
                case 'marcfinc':
                    return $this->linksFromMarcFullrecordService->getLinks($this->arguments['fullrecord'], $this->arguments['isil'], $this->arguments['unique']);
                    break;
                case 'ai':
                case 'is':
                    return $this->linksFromAiFullrecordService->getLinks($this->arguments['fullrecord'], $this->arguments['isil'], false);
                default:
                    return [];
            }
        }

    }

}

?>
