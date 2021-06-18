<?php

namespace Slub\SlubFindExtend\Services;
use TYPO3\CMS\Core\SingletonInterface;


/**
 * (c) 2013 http://stackoverflow.com/users/1696923/fazzyx
 *
 * From http://stackoverflow.com/questions/17440847/typo3-extbase-set-and-get-values-from-session
 *
 * Class SessionHandlerService
 * @package Slub\SlubFindExtend\Services
 */
class SessionHandlerService implements SingletonInterface
{
    private $prefixKey = 'slub_find_extend_';

    /**
     * Returns the object stored in the user´s PHP session
     * @return Object the stored object
     */
    public function restoreFromSession($key) {
        $sessionData = $GLOBALS['TSFE']->fe_user->getKey('ses', $this->prefixKey . $key);
        return unserialize($sessionData);
    }

    /**
     * Writes an object into the PHP session
     * @param    $object any serializable object to store into the session
     * @return   SessionHandlerService this
     */
    public function writeToSession($object, $key) {
        $sessionData = serialize($object);
        $GLOBALS['TSFE']->fe_user->setKey('ses', $this->prefixKey . $key, $sessionData);
        $GLOBALS['TSFE']->fe_user->storeSessionData();
        return $this;
    }

    /**
     * Cleans up the session: removes the stored object from the PHP session
     * @return   SessionHandlerService this
     */
    public function cleanUpSession($key) {
        $GLOBALS['TSFE']->fe_user->setKey('ses', $this->prefixKey . $key, NULL);
        $GLOBALS['TSFE']->fe_user->storeSessionData();
        return $this;
    }

    public function setPrefixKey($prefixKey) {
        $this->prefixKey = $prefixKey;
    }

}
