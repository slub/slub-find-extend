<?php
defined('TYPO3_MODE') or die();

/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');

// Hook into \Subugoe\Find\Controller
$signalSlotDispatcher->connect(
    'Subugoe\Find\Controller\SearchController',
    'detailActionBeforeRender',
    'Slub\SlubFindExtend\Slots\EnrichSolrResult',
    'detail',
    FALSE
);

// Hook into \Subugoe\Find\Controller
$signalSlotDispatcher->connect(
    'Subugoe\Find\Controller\SearchController',
    'indexActionBeforeRender',
    'Slub\SlubFindExtend\Slots\EnrichSolrResult',
    'index',
    FALSE
);

// Hook into \Subugoe\Find\Controller
$signalSlotDispatcher->connect(
    'Subugoe\Find\Controller\SearchController',
    'indexActionBeforeSelect',
    'Slub\SlubFindExtend\Slots\AdvancedQuery',
    'build',
    FALSE
);

// Hook into \Subugoe\Find\Controller
$signalSlotDispatcher->connect(
    'Subugoe\Find\Controller\SearchController',
    'detailActionBeforePagingSelect',
    'Slub\SlubFindExtend\Slots\AdvancedQuery',
    'build',
    FALSE
);

// Hook into \Subugoe\Find\Controller
$signalSlotDispatcher->connect(
    'Subugoe\Find\Controller\SearchController',
    'detailActionBeforeRender',
    'Slub\SlubFindExtend\Slots\ModifySolrResult',
    'decode',
    FALSE
);

// Hook into \Subugoe\Find\Controller
$signalSlotDispatcher->connect(
    'Subugoe\Find\Controller\SearchController',
    'detailActionBeforeRender',
    'Slub\SlubFindExtend\Slots\ModifySolrResult',
    'blacklist',
    FALSE
);

$cacheKey = 'resolv_link_electronic';
if( !is_array( $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations'][ $cacheKey ] ) ){
    $cacheConfig =  $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations'][ $cacheKey ] = array();
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations'][ $cacheKey ]['frontend'] = 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend';
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations'][ $cacheKey ]['backend'] = 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend';
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations'][ $cacheKey ]['options'] = array();
}