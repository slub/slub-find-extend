<?php
namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**

 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;


/**
 * GetUsernameViewHelper
 */
class GetDataFromWebserviceViewHelper extends AbstractViewHelper
{

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

    /**
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        if($arguments['url']) {

            $data = json_encode($arguments['data']);

            $ch = curl_init($arguments['url']);

            if($arguments['type'] === 'POST') {
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
