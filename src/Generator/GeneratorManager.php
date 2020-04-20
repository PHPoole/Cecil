<?php
/**
 * This file is part of the Cecil/Cecil package.
 *
 * Copyright (c) Arnaud Ligny <arnaud@ligny.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cecil\Generator;

use Cecil\Collection\Page\Collection as PagesCollection;
use Cecil\Util;

class GeneratorManager extends \SplPriorityQueue
{
    /**
     * Adds a generator.
     *
     * @param GeneratorInterface $generator
     * @param int                $priority
     *
     * @return self
     */
    public function addGenerator(GeneratorInterface $generator, int $priority = 1): self
    {
        $this->insert($generator, $priority);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function compare($priority1, $priority2)
    {
        if ($priority1 === $priority2) {
            return 0;
        }

        return $priority1 > $priority2 ? -1 : 1;
    }

    /**
     * Process each generators.
     *
     * @param PagesCollection $pagesCollection
     * @param \Closure        $messageCallback
     *
     * @return PagesCollection
     */
    public function process(PagesCollection $pagesCollection, \Closure $messageCallback): PagesCollection
    {
        $max = $this->count();

        if ($max > 0) {
            $this->top();
            while ($this->valid()) {
                $count = $max - $this->key();
                /** @var AbstractGenerator $generator */
                $generator = $this->current();
                /** @var PagesCollection $generatedPages */
                $generatedPages = $generator->runGenerate($pagesCollection, $messageCallback);
                foreach ($generatedPages as $page) {
                    /** @var \Cecil\Collection\Page\Page $page */
                    if ($pagesCollection->has($page->getId())) {
                        $pagesCollection->replace($page->getId(), $page);
                    } else {
                        $pagesCollection->add($page);
                    }
                }
                $message = sprintf('%s: %s', Util::formatClassName($generator), count($generatedPages));
                call_user_func_array($messageCallback, ['GENERATE_PROGRESS', $message, $count, $max]);
                $this->next();
            }
        }

        return $pagesCollection;
    }
}
