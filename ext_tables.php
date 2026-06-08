<?php

defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'slub_find_extend',
    'Configuration/TypoScript/ElasticsearchLogger',
    'SLUB Find Extend: Elasticsearch Logger'
);
