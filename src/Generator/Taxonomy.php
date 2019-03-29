<?php
/*
 * Copyright (c) Arnaud Ligny <arnaud@ligny.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cecil\Generator;

use Cecil\Collection\Page\Collection as PagesCollection;
use Cecil\Collection\Page\Page;
use Cecil\Collection\Page\Type;
use Cecil\Collection\Taxonomy\Collection as VocabulariesCollection;
use Cecil\Collection\Taxonomy\Term as Term;
use Cecil\Collection\Taxonomy\Vocabulary as Vocabulary;
use Cecil\Exception\Exception;

/**
 * Class Taxonomy.
 */
class Taxonomy extends AbstractGenerator implements GeneratorInterface
{
    /* @var VocabulariesCollection */
    protected $vocabulariesCollection;

    /**
     * {@inheritdoc}
     */
    public function generate(): void
    {
        if ($this->config->get('site.taxonomies')) {
            $this->createVocabulariesCollection();
            $this->collectTermsFromPages();
            $this->generateTaxonomiesPages();
        }
    }

    /**
     * Create a collection from the vocabularies configuration.
     */
    protected function createVocabulariesCollection()
    {
        // create an empty "taxonomies" collection
        $this->vocabulariesCollection = new VocabulariesCollection('taxonomies');

        // adds vocabularies to the collection
        foreach (array_keys($this->config->get('site.taxonomies')) as $vocabulary) {
            /*
             * ie:
             *   taxonomies:
             *     tags: disabled
             */
            if ($this->config->get("site.taxonomies.$vocabulary") == 'disabled') {
                continue;
            }

            $this->vocabulariesCollection->add(new Vocabulary($vocabulary));
        }
    }

    /**
     * Collects vocabularies/terms from pages frontmatter.
     */
    protected function collectTermsFromPages()
    {
        /* @var $page Page */
        foreach ($this->pagesCollection as $page) {
            foreach ($this->vocabulariesCollection as $vocabulary) {
                $plural = $vocabulary->getId();
                /*
                 * ie:
                 *   tags: Tag 1, Tag 2
                 */
                if ($page->hasVariable($plural)) {
                    // converts a string list to an array
                    if (!is_array($page->getVariable($plural))) {
                        $page->setVariable($plural, [$page->getVariable($plural)]);
                    }
                    // adds each term to the vocabulary collection...
                    foreach ($page->getVariable($plural) as $term) {
                        $term = mb_strtolower($term);
                        $this->vocabulariesCollection
                            ->get($plural)
                            ->add(new Term($term));
                        // ... and adds page to the term collection
                        $this->vocabulariesCollection
                            ->get($plural)
                            ->get($term)
                            ->add($page);
                    }
                }
            }
        }
    }

    /**
     * Generate taxonomies pages.
     */
    protected function generateTaxonomiesPages()
    {
        /* @var $vocabulary Vocabulary */
        foreach ($this->vocabulariesCollection as $position => $vocabulary) {
            $plural = $vocabulary->getId();
            if (count($vocabulary) > 0) {
                /*
                 * Creates $plural/$term pages (list of pages)
                 * ie: /tags/tag-1/
                 */
                /* @var $pages PagesCollection */
                foreach ($vocabulary as $position => $term) {
                    $pages = $term->sortByDate();
                    $pageId = $path = Page::slugify(sprintf('%s/%s', $plural, $term->getId()));
                    $page = (new Page($pageId))->setVariable('title', ucfirst($term->getId()));
                    if ($this->pagesCollection->has($pageId)) {
                        $page = clone $this->pagesCollection->get($pageId);
                    }
                    $date = $pages->first()->getVariable('date');
                    $page->setPath($path)
                        ->setType(Type::TAXONOMY_VOCABULARY)
                        ->setVariable('pages', $pages)
                        ->setVariable('date', $date)
                        ->setVariable('singular', $this->config->get("site.taxonomies.$plural"))
                        ->setVariable('pagination', ['pages' => $pages]);
                    $this->generatedPages->add($page);
                }
                /*
                 * Creates $plural pages (list of terms)
                 * ex: /tags/
                 */
                $page = (new Page(Page::slugify($plural)))
                    ->setPath(strtolower($plural))
                    ->setVariable('title', $plural)
                    ->setType(Type::TAXONOMY_TERMS)
                    ->setVariable('plural', $plural)
                    ->setVariable('singular', $this->config->get("site.taxonomies.$plural"))
                    ->setVariable('terms', $vocabulary)
                    ->setVariable('date', $date);
                // add page only if a template exist
                try {
                    $this->generatedPages->add($page);
                } catch (Exception $e) {
                    printf("%s\n", $e->getMessage());
                    unset($page); // do not add page
                }
            }
        }
    }
}
