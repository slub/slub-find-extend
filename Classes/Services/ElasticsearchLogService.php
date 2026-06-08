<?php

namespace Slub\SlubFindExtend\Services;

use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Sends search query logs to an Elasticsearch index.
 */
class ElasticsearchLogService
{
    private const PORTAL = 'slub-katalog';

    private const INDEX_PREFIX = 'catalog-search-queries';

    private const MIN_TIMEOUT_SECONDS = 0.3;

    private const MAX_TIMEOUT_SECONDS = 0.5;

    /**
     * @var array<string, string>
     */
    private const QUERY_FIELD_MAP = [
        'default' => 'all',
        'author' => 'person_institution',
        'author2' => 'person_institution',
        'author_corporate' => 'person_institution',
        'title' => 'title',
        'topic' => 'subject_keyword',
        'barcode' => 'barcode',
        'ident' => 'identifier',
        'isbn' => 'identifier',
        'issn' => 'identifier',
        'ismn' => 'identifier',
        'doi' => 'identifier',
        'ppn' => 'identifier',
        'oclc' => 'identifier',
        'rsn' => 'identifier',
        'rvk' => 'rvk',
        'rvk_facet' => 'rvk',
        'signatur' => 'shelfmark',
        'shelfmark' => 'shelfmark',
        'imprint' => 'publisher_place',
        'publisher' => 'publisher_place',
        'publishplace' => 'publisher_place',
        'series2' => 'series',
        'series' => 'series',
        'provenance' => 'provenance',
    ];

    protected string $elasticsearchUrl;

    protected float $timeout;

    protected string $username;

    protected string $password;

    protected bool $verifySsl;

    protected int $deduplicateWindowSeconds;

    public function __construct(string $elasticsearchUrl = '', string $indexName = 'find-search-log', float $timeout = 0.4)
    {
        $extensionConfiguration = (array)($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['slub_find_extend'] ?? []);

        $this->elasticsearchUrl = rtrim($elasticsearchUrl ?: ($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['slub_find_extend']['elasticsearchLogUrl'] ?? 'http://localhost:9200'), '/');

        $configuredTimeout = $timeout > 0
            ? $timeout
            : (float)($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['slub_find_extend']['elasticsearchLogTimeout'] ?? 0.4);

        // Keep the logging timeout short so the search request is not delayed.
        $this->timeout = max(self::MIN_TIMEOUT_SECONDS, min(self::MAX_TIMEOUT_SECONDS, $configuredTimeout));

        $this->username = trim((string)($extensionConfiguration['elasticsearchLogUsername'] ?? ''));
        $this->password = (string)($extensionConfiguration['elasticsearchLogPassword'] ?? '');
        $this->verifySsl = (bool)($extensionConfiguration['elasticsearchLogVerifySsl'] ?? true);
        $this->deduplicateWindowSeconds = max(0, (int)($extensionConfiguration['elasticsearchLogDeduplicateWindowSeconds'] ?? 25));
    }

    /**
     * Logs a search event to Elasticsearch.
     *
     * @param array<string, mixed> $query             Parsed query arguments (typically tx_find_find[q]).
     * @param int                  $numFound          Total result count.
     * @param array<string, mixed> $requestArguments  Full tx_find_find request arguments.
     * @param int|null             $responseTimeMs    Solr response time in milliseconds.
     */
    public function logSearch(array $query, int $numFound, array $requestArguments = [], ?int $responseTimeMs = null): void
    {
        $document = $this->buildDocument($query, $numFound, $requestArguments, $responseTimeMs);

        $indexName = self::INDEX_PREFIX . '-' . date('Y.m');
        $eventId = $this->buildEventId($document);
        $method = 'POST';
        $url = sprintf('%s/%s/_doc', $this->elasticsearchUrl, $indexName);
        if ($eventId !== null) {
            $method = 'PUT';
            $url = sprintf('%s/%s/_doc/%s', $this->elasticsearchUrl, $indexName, rawurlencode($eventId));
        }

        try {
            /** @var RequestFactory $requestFactory */
            $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
            $requestOptions = [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => json_encode($document, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'timeout' => $this->timeout,
                'verify'  => $this->verifySsl,
            ];

            if ($this->username !== '' && $this->password !== '') {
                $requestOptions['auth'] = [$this->username, $this->password];
            }

            $requestFactory->request(
                $url,
                $method,
                $requestOptions
            );
        } catch (\Throwable $e) {

            // Logging-Fehler darf die Suchanfrage nicht unterbrechen.
            $logger = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger(__CLASS__);
            $logger->warning('Elasticsearch search log failed: ' . $e->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $requestArguments
     * @return array<string, mixed>
     */
    private function buildDocument(array $query, int $numFound, array $requestArguments, ?int $responseTimeMs): array
    {
        $queryRows = $this->extractQueryRows($query);
        $isSimpleAllSearch = count($queryRows) === 1 && ($queryRows[0]['field'] ?? null) === 'all';
        $selectedFacets = $this->extractSelectedFacets($requestArguments);

        $primaryQuery = $isSimpleAllSearch ? $queryRows[0]['query'] : null;
        $normalizedPrimaryQuery = $isSimpleAllSearch ? $queryRows[0]['normalized_query'] : null;

        $page = $this->toNullableInt($requestArguments['page'] ?? null);
        $pageSize = $this->toNullableInt($requestArguments['count'] ?? null);

        return [
            '@timestamp' => date('c'),
            'portal' => self::PORTAL,
            'environment' => $this->resolveEnvironment(),
            'search_type' => $isSimpleAllSearch ? 'simple' : 'advanced',
            'is_all_search' => $isSimpleAllSearch,
            'primary_query' => $primaryQuery,
            'normalized_primary_query' => $normalizedPrimaryQuery,
            'primary_scope' => $isSimpleAllSearch ? 'all' : null,
            'fields' => $queryRows,
            'field_count' => count($queryRows),
            'used_fields' => array_values(array_unique(array_column($queryRows, 'field'))),
            'selected_facets' => $selectedFacets,
            'selected_facet_count' => count($selectedFacets),
            'selected_facet_ids' => array_values(array_unique(array_column($selectedFacets, 'facet'))),
            'result_count' => $numFound,
            'zero_results' => $numFound === 0,
            'page' => $page,
            'page_size' => $pageSize,
            'response_time_ms' => $responseTimeMs,
            'intent' => null,
            'intent_confidence' => null,
            'search_strategy' => null,
            'metadata' => [
                'request_id' => $this->resolveRequestId(),
                'session_hash' => $this->resolveSessionHash(),
                'language' => $this->resolveLanguage(),
                'device' => $this->resolveDevice(),
            ],
        ];
    }

    /**
     * Build a deterministic ID for a short time window to avoid duplicate
     * documents when the same search request is triggered repeatedly.
     *
     * @param array<string, mixed> $document
     */
    private function buildEventId(array $document): ?string
    {
        if ($this->deduplicateWindowSeconds <= 0) {
            return null;
        }

        $bucket = (int)floor(time() / $this->deduplicateWindowSeconds);
        $payload = [
            'environment' => $document['environment'] ?? null,
            'fields' => $document['fields'] ?? [],
            'selected_facets' => $document['selected_facets'] ?? [],
            'page' => $document['page'] ?? null,
            'page_size' => $document['page_size'] ?? null,
            'session_hash' => $document['metadata']['session_hash'] ?? null,
            'bucket' => $bucket,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json) || $json === '') {
            return null;
        }

        return hash('sha256', $json);
    }

    /**
     * @param array<string, mixed> $query
     * @return array<int, array<string, int|string|null>>
     */
    private function extractQueryRows(array $query): array
    {
        $rows = [];
        $position = 1;

        foreach ($query as $rawField => $rawValue) {
            $field = $this->mapField($rawField);
            if ($field === null) {
                continue;
            }

            $values = is_array($rawValue) ? $rawValue : [$rawValue];
            foreach ($values as $value) {
                $queryValue = $this->sanitizeQuery($value);
                if ($queryValue === null) {
                    continue;
                }

                $rows[] = [
                    'field' => $field,
                    'query' => $queryValue,
                    'normalized_query' => $this->normalizeQuery($queryValue),
                    'position' => $position,
                    'operator' => $position === 1 ? null : 'AND',
                ];
                $position++;
            }
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $requestArguments
     * @return array<int, array<string, string>>
     */
    private function extractSelectedFacets(array $requestArguments): array
    {
        $facets = [];
        $rawFacets = $requestArguments['facet'] ?? null;

        if (!is_array($rawFacets)) {
            return $facets;
        }

        foreach ($rawFacets as $facetId => $facetSelection) {
            if (!is_array($facetSelection)) {
                continue;
            }

            $facetId = trim((string)$facetId);
            if ($facetId === '') {
                continue;
            }

            foreach ($facetSelection as $term => $status) {
                $term = trim(urldecode((string)$term));
                if ($term === '') {
                    continue;
                }

                $status = trim((string)$status);
                if ($status === '') {
                    $status = '1';
                }

                $facets[] = [
                    'facet' => $facetId,
                    'term' => $term,
                    'status' => $status,
                ];
            }
        }

        return $facets;
    }

    private function mapField(string $field): ?string
    {
        $normalized = strtolower(trim($field));
        return self::QUERY_FIELD_MAP[$normalized] ?? null;
    }

    /**
     * @param mixed $value
     */
    private function sanitizeQuery($value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $query = trim((string)$value);
        return $query === '' ? null : $query;
    }

    private function normalizeQuery(string $query): string
    {
        $query = trim($query);
        $query = preg_replace('/\s+/', ' ', $query) ?? $query;
        return mb_strtolower($query, 'UTF-8');
    }

    private function resolveEnvironment(): string
    {
        $context = (string)(getenv('TYPO3_CONTEXT') ?: ($_SERVER['TYPO3_CONTEXT'] ?? ''));
        if ($context === '') {
            $context = (string)($GLOBALS['TYPO3_CONF_VARS']['SYS']['applicationContext'] ?? '');
        }

        $context = trim($context);
        return $context !== '' ? $context : 'Production';
    }

    private function resolveRequestId(): ?string
    {
        $server = $_SERVER;

        $requestId = $server['HTTP_X_REQUEST_ID']
            ?? $server['HTTP_X_CORRELATION_ID']
            ?? null;

        $requestId = is_scalar($requestId) ? trim((string)$requestId) : '';
        return $requestId !== '' ? $requestId : null;
    }

    private function resolveSessionHash(): ?string
    {
        $cookieName = session_name();
        if ($cookieName === '') {
            return null;
        }

        $sessionId = $_COOKIE[$cookieName] ?? null;
        if (!is_string($sessionId) || trim($sessionId) === '') {
            return null;
        }

        $encryptionKey = (string)($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] ?? '');
        if ($encryptionKey === '') {
            return null;
        }

        return hash('sha256', $encryptionKey . '|' . $sessionId);
    }

    private function resolveLanguage(): ?string
    {
        $language = (string)($GLOBALS['TSFE']->config['config']['language'] ?? '');
        $language = strtolower(trim($language));

        if ($language === '') {
            return null;
        }

        if (str_starts_with($language, 'de')) {
            return 'de';
        }

        if (str_starts_with($language, 'en')) {
            return 'en';
        }

        return null;
    }

    private function resolveDevice(): ?string
    {
        $userAgent = strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
        if ($userAgent === '') {
            return null;
        }

        if (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'tablet';
        }

        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android') || str_contains($userAgent, 'iphone')) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * @param mixed $value
     */
    private function toNullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_scalar($value) || !is_numeric((string)$value)) {
            return null;
        }

        return (int)$value;
    }
}
