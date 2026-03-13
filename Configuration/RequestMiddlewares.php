<?php

return [
    'frontend' => [
        'slub/slub-find-extend/fix-bracket-query-params' => [
            'target' => \Slub\SlubFindExtend\Middleware\FixBracketInQueryParamsMiddleware::class,
            // Run very early, before cHash validation and routing
            'before' => [
                'typo3/cms-frontend/page-resolver',
                'typo3/cms-frontend/static-route-resolver',
            ],
            'after' => [
                'typo3/cms-core/normalized-params-attribute',
            ],
        ],
    ],
];