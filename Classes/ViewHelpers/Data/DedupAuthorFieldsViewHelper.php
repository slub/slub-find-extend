<?php

namespace Slub\SlubFindExtend\ViewHelpers\Data;

/**
 * DedupAuthorFieldsVideHelper
 *
 * Remove deduplicate author fields from document
 *
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class DedupAuthorFieldsViewHelper extends AbstractViewHelper
{
    /**
     * Register arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('document', 'mixed', 'The document to dedup authors within', false, null);
    }

    /**
     * Remove deduplicate author fields from document
     *
     * @param mixed $document Content string
     * @return mixed
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $document = $arguments['document'];

        if ($document === null) {
            $document = $renderChildrenClosure();
        }

        $documentcopy['fields'] = $document->getFields();

        $author = $documentcopy['fields']['author'];
        $author_role = $documentcopy['fields']['author_role'];
        $author2 = $documentcopy['fields']['author2'];
        $author2_role = $documentcopy['fields']['author2_role'];

        $authorcache = [];
        $newauthor2 = [];
        $newauthor2_role = [];

        if ($author) {
            $i = 0;
            foreach ($author as $author_iterate) {
                $authorcache[] = $author_iterate . $author_role[$i];
                $authorcache[] = $author_iterate;
                $i++;
            }
        }

        if ($author2) {
            $i = 0;

            foreach ($author2 as $author2_iterate) {
                if (!in_array($author2_iterate . $author2_role[$i], $authorcache)) {
                    $newauthor2[] = $author2_iterate;
                    $newauthor2_role[] = $author2_role[$i];
                    $authorcache[] = $author2_iterate . $author2_role[$i];
                    $authorcache[] = $author2_iterate;
                }

                $i++;
            }

            $documentcopy['fields']['author2'] = $newauthor2;
            $documentcopy['fields']['author2_role'] = $newauthor2_role;
        }

        return $documentcopy;
    }
}
