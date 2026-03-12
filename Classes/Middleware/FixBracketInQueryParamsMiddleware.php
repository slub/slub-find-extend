<?php

declare(strict_types=1);

namespace Slub\SlubFindExtend\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to fix bracket encoding in query parameters for faceted search.
 * 
 * This middleware handles the proper encoding of brackets in facet values that themselves
 * contain brackets. When facet values include brackets (e.g., "Egen[olff] (Verleger)"),
 * PHP's parse_str() would interpret these as array notation, leading to incorrect parsing.
 * 
 * The middleware:
 * - Identifies structural array brackets (e.g., tx_find_find[facet][author][...])
 * - Double-encodes brackets within the actual facet values (%255B instead of %5B)
 * - This ensures parse_str() decodes them once, leaving them as %5B in the array keys
 * - Further processing in the application then decodes them for display and Solr queries
 * 
 * @package Slub\SlubFindExtend\Middleware
 */

class FixBracketInQueryParamsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $rawQuery = $uri->getQuery();

        if ($rawQuery !== '' && (str_contains($rawQuery, '%5D') || str_contains($rawQuery, ']'))) {

            $fixedQuery = $this->fixQueryString($rawQuery);

            if ($fixedQuery !== $rawQuery) {
                $request = $request
                    ->withUri($uri->withQuery($fixedQuery))
                    ->withQueryParams($this->parseQueryString($fixedQuery));
            }
        }

        return $handler->handle($request);
    }

    /**
     * Fix query string by decoding only structural array brackets.
     * 
     * Strategy: Parse the array structure using regex to find proper array keys,
     * then encode any brackets that appear within the actual key values.
     */
    private function fixQueryString(string $query): string
    {        
        $parts = explode('&', $query);
        $fixed = [];

        foreach ($parts as $part) {
            $eqPos = strpos($part, '=');
            if ($eqPos === false) {
                $fixed[] = $part;
                continue;
            }

            $key   = substr($part, 0, $eqPos);
            $value = substr($part, $eqPos + 1);

            $keyDecoded = str_replace(['%5B', '%5D', '%5b', '%5d'], ['[', ']', '[', ']'], $key);

            $fixedKey = $this->fixBracketsInKey($keyDecoded);

            $fixed[] = $fixedKey . '=' . $value;
        }

        return implode('&', $fixed);
    }

    /**
     * Fix brackets by double-encoding those that are part of the key value itself,
     * not the array structure.
     * 
     * Strategy: Split by ][ to identify array segments, then double-encode 
     * any remaining brackets within each segment (because parse_str will decode once).
     */
    private function fixBracketsInKey(string $key): string
    {
        $firstBracket = strpos($key, '[');
        if ($firstBracket === false) {
            return $key; 
        }
        
        $baseName = substr($key, 0, $firstBracket);
        $arrayPart = substr($key, $firstBracket);
        
        $arrayPart = ltrim($arrayPart, '[');
        $arrayPart = rtrim($arrayPart, ']');
        
        $segments = explode('][', $arrayPart);
        
        $fixedSegments = array_map(function($segment) {
            return str_replace(['[', ']'], ['%255B', '%255D'], $segment);
        }, $segments);

        return $baseName . '[' . implode('][', $fixedSegments) . ']';
    }

    private function parseQueryString(string $query): array
    {
        parse_str($query, $params);
        return $params;
    }
}