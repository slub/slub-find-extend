<?php

namespace Slub\SlubFindExtend\Routing\Aspect;

/**
 * simple dummy class to remove cHashes from routing
 *
*/

use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;
use TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;

class SlubCatalogId implements StaticMappableAspectInterface
{
    public function generate(string $id): ?string
    {
        return empty($id) ? null : (string) $id;
    }

    public function resolve(string $id): ?string
    {
        return empty($id) ? null : (string) $id;
    }
}
