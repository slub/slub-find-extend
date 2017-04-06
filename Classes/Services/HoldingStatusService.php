<?php

namespace Slub\SlubFindExtend\Services;

use Solarium\QueryType\Select\Result\Document;

/**
 * Class StatusService
 * @package Slub\SlubFindExtend\Services
 */
class HoldingStatusService {

    /**
     * Returns the holding state
     *
     * @param $exemplare
     * @return int
     */
    private function getLocalHoldingStatusFromArray($exemplare) {

        $status = 9999;

        foreach ($exemplare as $exemplar) {

            if(!is_array($exemplar)) {
                $exemplar = (array)$exemplar;
            }
            if ( $status != 1) {
                if ($exemplar['elements'] && is_array($exemplar['elements'])) {
                    $status = $this->getLocalHoldingStatusFromArray($exemplar['elements']);
                } elseif ($exemplar['_calc_colorcode'] < $status) {
                    if (!($exemplar['_calc_colorcode'] == 0 && ($status == 2))) {
                        $status = $exemplar['_calc_colorcode'];
                    }
                }
            }
        }

        // 0 = (i)nfo
        if($status === 9999) {
            $status = 0;
        }

        return $status;

    }

    /**
     * Returns the status code
     *
     * @param Document $document
     * @param mixed $copies NULL
     * @return int
     */
    public function getStatus(Document $document, $copies = []) {

        // Electronic Resource are always accessible. Might needs fine tuning furtehr on.
        if($document['access_facet'] === 'Electronic Resources') {
            return 1;
        } else {
            return $this->getLocalHoldingStatusFromArray($copies);
        }

    }

}
