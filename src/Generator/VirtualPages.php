<?php
/*
 * Copyright (c) Arnaud Ligny <arnaud@ligny.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cecil\Generator;

use Cecil\Collection\Page\Page;
use Cecil\Collection\Page\Type;
use Cecil\Exception\Exception;

/**
 * Class Generator\VirtualPages.
 */
class VirtualPages extends AbstractGenerator implements GeneratorInterface
{
    protected $configKey = 'virtualpages';

    /**
     * {@inheritdoc}
     */
    public function generate(): void
    {
        $pagesConfig = $this->collectPagesFromConfig($this->configKey);

        if (!$pagesConfig) {
            return;
        }
        if (!is_array($pagesConfig)) {
            throw new Exception(sprintf('Config key "%s" is not set.', $this->configKey));
        }

        foreach ($pagesConfig as $frontmatter) {
            if (isset($frontmatter['published']) && $frontmatter['published'] === false) {
                continue;
            }
            if (!array_key_exists('path', $frontmatter)) {
                throw new Exception(sprintf(
                    'Each pages in "%s" config\'s section must have a "path".',
                    $this->configKey
                ));
            }
            $path = Page::slugify($frontmatter['path']);
            $id = !empty($path) ? $path : 'index';
            // abort if already exists...
            if ($this->pagesCollection->has($id)) {
                continue;
            }
            $page = (new Page($id))
                ->setPath($path)
                ->setType(Type::PAGE);
            $page->setVariables($frontmatter);
            $this->generatedPages->add($page);
        }
    }

    /**
     * Collect "virtual pages" config.
     *
     * @param string $configKey
     *
     * @return array|null
     */
    private function collectPagesFromConfig(string $configKey): ?array
    {
        return $this->config->get($configKey);
    }
}
