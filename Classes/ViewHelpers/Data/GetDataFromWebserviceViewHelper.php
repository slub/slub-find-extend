<?php
namespace Slub\SlubFindExtend\ViewHelpers\Data;

    /***************************************************************
     *
     *  Copyright notice
     *
     *  This script is part of the TYPO3 project. The TYPO3 project is
     *  free software; you can redistribute it and/or modify
     *  it under the terms of the GNU General Public License as published by
     *  the Free Software Foundation; either version 3 of the License, or
     *  (at your option) any later version.
     *
     *  The GNU General Public License can be found at
     *  http://www.gnu.org/copyleft/gpl.html.
     *
     *  This script is distributed in the hope that it will be useful,
     *  but WITHOUT ANY WARRANTY; without even the implied warranty of
     *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *  GNU General Public License for more details.
     *
     *  This copyright notice MUST APPEAR in all copies of the script!
     ***************************************************************/

/**
 * GetUsernameViewHelper
 */
class GetDataFromWebserviceViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('url', 'string', 'The URL to query', FALSE, NULL);
        $this->registerArgument('data', 'object', 'The object representaion of the data send to the server', FALSE, NULL);
        $this->registerArgument('type', 'string', 'Use get or post to query data', FALSE, NULL);
    }

    public function render (){

        if($this->arguments['url']) {

            $data = json_encode($this->arguments['data']);

            $ch = curl_init($this->arguments['url']);

            if($this->arguments['type'] === 'POST') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data))
                );
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            return json_decode($result);

        }

        return FALSE;

    }

}