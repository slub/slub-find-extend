<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class HierarchySortKeyViewHelper extends AbstractViewHelper
{
    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('document', 'array', 'Solr document data.', true);
        $this->registerArgument('documents', 'array', 'Solr documents to sort.', true);
        $this->registerArgument('fallbackField', 'string', 'Fallback sort field if no hierarchy sort value exists.', false, 'publishDateSort');
    }

    /**
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $document = $arguments['document'] ?? [];
        $documents = $arguments['documents'] ?? [];
        $fallbackField = trim((string)($arguments['fallbackField'] ?? 'publishDateSort'));

        if (!is_array($document) || !is_array($documents) || $documents === []) {
            return ['documents' => $sortedDocuments];
        }

        $resolvedRows = [];
        $useHierarchySort = true;
        $currentId = static::normalizeScalar($document['id'] ?? '');

        foreach ($documents as $originalIndex => $solrDocument) {
            $hierarchySortValue = static::resolveHierarchySortValue($solrDocument, $currentId);
            $fallbackSortValue = static::resolveFieldValue($solrDocument, $fallbackField);

            if ($hierarchySortValue === '') {
                $useHierarchySort = false;
            }

            $resolvedRows[] = [
                'document' => $solrDocument,
                'hierarchySortValue' => $hierarchySortValue,
                'fallbackSortValue' => $fallbackSortValue,
                'originalIndex' => $originalIndex,
            ];
        }

        $sortKey = $useHierarchySort ? 'hierarchySortValue' : 'fallbackSortValue';

        usort($resolvedRows, function (array $leftRow, array $rightRow) use ($sortKey) {
            $leftValue = (string)($leftRow[$sortKey] ?? '');
            $rightValue = (string)($rightRow[$sortKey] ?? '');

            if ($leftValue === $rightValue) {
                return $leftRow['originalIndex'] <=> $rightRow['originalIndex'];
            }

            if ($sortKey === 'hierarchySortValue') {
                return static::compareHierarchySortValues($leftValue, $rightValue);
            }

            return strcmp($rightValue, $leftValue);
        });

        $sortedDocuments = array_values(array_map(function (array $row) {
            return $row['document'];
        }, $resolvedRows));

        return ['documents' => $sortedDocuments];
    }

    private static function resolveHierarchySortValue($solrDocument, string $currentId): string
    {
        if ($currentId === '') {
            return '';
        }

        $parentIds = static::resolveFieldList($solrDocument, 'hierarchy_parent_id');
        $sortValues = static::resolveFieldList($solrDocument, 'sort_in_hierarchy_str_mv');

        if ($parentIds === [] || $sortValues === [] || count($parentIds) !== count($sortValues)) {
            return '';
        }

        $index = array_search((string)$currentId, array_map('strval', $parentIds), true);
        if ($index === false || !array_key_exists($index, $sortValues)) {
            return '';
        }

        return trim((string)$sortValues[$index]);
    }

    private static function compareHierarchySortValues(string $leftValue, string $rightValue): int
    {
        $leftSort = static::buildHierarchySortInfo($leftValue);
        $rightSort = static::buildHierarchySortInfo($rightValue);

        if ($leftSort['category'] !== $rightSort['category']) {
            return $leftSort['category'] <=> $rightSort['category'];
        }

        if ($leftSort['category'] === 0 || $leftSort['category'] === 1) {
            $leftTokens = static::tokenizeMixedSortValue($leftValue);
            $rightTokens = static::tokenizeMixedSortValue($rightValue);

            if ($leftTokens[0]['value'] !== $rightTokens[0]['value']) {
                return $rightTokens[0]['value'] <=> $leftTokens[0]['value'];
            }

            $maxLength = max(count($leftTokens), count($rightTokens));

            for ($index = 1; $index < $maxLength; $index++) {
                $leftToken = $leftTokens[$index] ?? null;
                $rightToken = $rightTokens[$index] ?? null;

                if ($leftToken === null && $rightToken === null) {
                    break;
                }

                if ($leftToken === null) {
                    return 1;
                }

                if ($rightToken === null) {
                    return -1;
                }

                if ($leftToken['type'] !== $rightToken['type']) {
                    return $leftToken['type'] === 'number' ? -1 : 1;
                }

                if ($leftToken['value'] === $rightToken['value']) {
                    continue;
                }

                if ($leftToken['type'] === 'number') {
                    return $rightToken['value'] <=> $leftToken['value'];
                }

                return strcmp($rightToken['value'], $leftToken['value']);
            }

            return strcmp($rightValue, $leftValue);
        }

        $leftTokens = $leftSort['tokens'];
        $rightTokens = $rightSort['tokens'];
        if ($leftTokens[0] !== $rightTokens[0]) {
            return $rightTokens[0] <=> $leftTokens[0];
        }

        if (count($leftTokens) !== count($rightTokens)) {
            return count($rightTokens) <=> count($leftTokens);
        }

        $maxLength = max(count($leftTokens), count($rightTokens));

        for ($index = 1; $index < $maxLength; $index++) {
            $leftToken = $leftTokens[$index] ?? null;
            $rightToken = $rightTokens[$index] ?? null;

            if ($leftToken === $rightToken) {
                continue;
            }

            if ($leftToken === null) {
                return 1;
            }

            if ($rightToken === null) {
                return -1;
            }

            return $rightToken <=> $leftToken;
        }

        return strcmp($rightValue, $leftValue);
    }

    private static function buildHierarchySortInfo(string $value): array
    {
        $normalized = trim($value);

        if ($normalized === '') {
            return [
                'category' => 2,
                'tokens' => [],
            ];
        }

        if (preg_match('/^[a-zA-Z]/', $normalized)) {
            return [
                'category' => 0,
                'tokens' => [$normalized], 
            ];
        }

        preg_match_all('/\d+/', $normalized, $matches);
        $tokens = array_map('intval', $matches[0] ?? []);

        if ($tokens === []) {
            return [
                'category' => 2,
                'tokens' => [$normalized],
            ];
        }

        return [
            'category' => 1,
            'tokens' => $tokens,
        ];
    }

    private static function tokenizeMixedSortValue(string $value): array
    {
        preg_match_all('/\d+|[A-Za-z]+/', $value, $matches);

        return array_map(function (string $token): array {
            if (ctype_digit($token)) {
                return [
                    'type' => 'number',
                    'value' => (int)$token,
                ];
            }

            return [
                'type' => 'text',
                'value' => $token,
            ];
        }, $matches[0] ?? []);
    }

    private static function resolveFieldValue($document, string $fieldName): string
    {
        if (is_array($document) && array_key_exists($fieldName, $document)) {
            $value = $document[$fieldName];
        } elseif ($document instanceof \ArrayAccess && isset($document[$fieldName])) {
            $value = $document[$fieldName];
        } elseif (is_object($document) && isset($document->$fieldName)) {
            $value = $document->$fieldName;
        } else {
            return '';
        }

        if (is_array($value)) {
            return trim((string)reset($value));
        }

        return trim((string)$value);
    }

    private static function resolveFieldList($document, string $fieldName): array
    {
        if (is_array($document) && array_key_exists($fieldName, $document)) {
            $value = $document[$fieldName];
        } elseif ($document instanceof \ArrayAccess && isset($document[$fieldName])) {
            $value = $document[$fieldName];
        } elseif (is_object($document) && isset($document->$fieldName)) {
            $value = $document->$fieldName;
        } else {
            return [];
        }

        if ($value === null) {
            return [];
        }

        if (!is_array($value)) {
            return [trim((string)$value)];
        }

        return array_values(array_map(function ($entry) {
            return trim((string)$entry);
        }, $value));
    }

    private static function normalizeScalar($value): string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        return trim((string)$value);
    }
}
