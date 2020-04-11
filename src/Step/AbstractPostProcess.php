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

use Cecil\Cache\Cache;
use Cecil\Exception\Exception;
use Cecil\Util;
use Symfony\Component\Finder\Finder;

/**
 * Post Processing.
 */
abstract class AbstractPostProcess extends AbstractStep
{
    /** @var string File type (ie: 'css') */
    protected $type = 'type';
    /** @var mixed File processor */
    protected $processor;

    /**
     * {@inheritdoc}
     */
    public function init($options)
    {
        if ($options['dry-run']) {
            $this->process = false;

            return;
        }
        if (false === $this->builder->getConfig()->get(sprintf('postprocess.%s.enabled', $this->type))) {
            $this->process = false;

            return;
        }
        if (true === $this->builder->getConfig()->get('postprocess.enabled')) {
            $this->process = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        $this->setProcessor();

        call_user_func_array(
            $this->builder->getMessageCb(),
            ['POSTPROCESS', sprintf('Post-processing %s', $this->type)]
        );

        $extensions = $this->builder->getConfig()->get(sprintf('postprocess.%s.ext', $this->type));
        if (empty($extensions)) {
            throw new Exception(sprintf('The config key "postprocess.%s.ext" is empty', $this->type));
        }

        $files = Finder::create()
            ->files()
            ->in($this->builder->getConfig()->getOutputPath())
            ->name('/\.('.implode('|', $extensions).')$/')
            ->notName('/\.min\.('.implode('|', $extensions).')$/')
            ->sortByName(true);
        $max = count($files);

        if ($max <= 0) {
            $message = 'No files';
            call_user_func_array($this->builder->getMessageCb(), ['POSTPROCESS_PROGRESS', $message]);

            return;
        }

        $count = 0;
        $postprocessed = 0;

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($files as $file) {
            $count++;
            $sizeBefore = $file->getSize();
            $message = $file->getRelativePathname();

            $cache = new Cache($this->builder, 'postprocess', $this->config->getOutputPath());
            if (!$cache->isExists($file->getPathname())) {
                $hash = $cache->getHash($file->getPathname());
                $this->processFile($file);
                $postprocessed++;
                $sizeAfter = $file->getSize();
                if ($sizeAfter < $sizeBefore) {
                    $message = sprintf(
                        '%s (%s Ko -> %s Ko)',
                        $file->getRelativePathname(),
                        ceil($sizeBefore / 1000),
                        ceil($sizeAfter / 1000)
                    );
                }
                $cache->save($file->getPathname(), $hash);
            }
            call_user_func_array($this->builder->getMessageCb(), ['POSTPROCESS_PROGRESS', $message, $count, $max]);
        }
        if ($postprocessed == 0) {
            $message = '> Nothing to do';
            call_user_func_array($this->builder->getMessageCb(), ['POSTPROCESS_PROGRESS', $message]);
        }
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function preparesHashFile(string $path): string
    {
        return str_replace(DIRECTORY_SEPARATOR, '-', $path).'_';
    }

    /**
     * @param string $path
     *
     * @return void
     */
    private function removesHashFile(string $path): void
    {
        $pattern = Util::joinFile($this->config->getCachePath(), self::CACHE_HASH, $this->preparesHashFile($path)).'*';
        foreach (glob($pattern) as $filename) {
            Util::getFS()->remove($filename);
        }
    }

    /**
     * Set file processor object.
     *
     * @return void
     */
    abstract public function setProcessor();

    /**
     * Process a file.
     *
     * @param \Symfony\Component\Finder\SplFileInfo $file
     *
     * @return void
     */
    abstract public function processFile(\Symfony\Component\Finder\SplFileInfo $file);
}
