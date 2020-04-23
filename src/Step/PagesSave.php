<?php
/**
 * This file is part of the Cecil/Cecil package.
 *
 * Copyright (c) Arnaud Ligny <arnaud@ligny.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cecil\Step;

use Cecil\Collection\Page\Page;
use Cecil\Exception\Exception;
use Cecil\Util;

/**
 * Pages saving.
 */
class PagesSave extends AbstractStep
{
    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function init($options)
    {
        if ($options['dry-run']) {
            $this->process = false;

            return;
        }

        Util::getFS()->mkdir($this->config->getOutputPath());

        $this->process = true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function process()
    {
        $this->builder->getLogger()->notice('Saving pages');

        /** @var Page $page */
        $filteredPages = $this->builder->getPages()->filter(function (Page $page) {
            return !empty($page->getVariable('rendered'));
        });
        $max = count($filteredPages);

        $count = 0;
        foreach ($filteredPages as $page) {
            $count++;
            $message = [];

            foreach ($page->getVariable('rendered') as $format => $rendered) {
                if (false === $pathname = $page->getOutputFile($format, $this->config)) {
                    throw new Exception(sprintf(
                        "Can't get pathname of page '%s' (format: '%s')",
                        $page->getId(),
                        $format
                    ));
                }
                $pathname = $this->cleanPath(Util::joinFile($this->config->getOutputPath(), $pathname));

                try {
                    Util::getFS()->dumpFile($pathname, $rendered['output']);
                } catch (\Exception $e) {
                    throw new Exception($e->getMessage());
                }

                $message[] = substr($pathname, strlen($this->config->getDestinationDir()) + 1);
            }

            $message = implode(', ', $message);
            $this->builder->getLogger()->info($message, ['progress' => [$count, $max]]);
        }
    }

    /**
     * Removes unnecessary directory separators.
     *
     * @param string $pathname
     *
     * @return string
     */
    protected function cleanPath($pathname): string
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            $pathname = preg_replace('#\\\\+#', '\\', $pathname);
        }

        return preg_replace('#/+#', '/', $pathname);
    }
}
