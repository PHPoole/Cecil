<?php
/*
 * This file is part of the PHPoole package.
 *
 * Copyright (c) Arnaud Ligny <arnaud@ligny.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPoole\Command;

use PHPoole\Util\Plateform;
use Zend\Console\Prompt\Confirm;

class NewWebsite extends AbstractCommand
{
    /**
     * @var bool
     */
    protected $force;

    public function processCommand()
    {
        $this->force = $this->getRoute()->getMatchedParam('force', false);

        try {
            if ($this->fs->exists($this->getPath().'/'.self::CONFIG_FILE) && !$this->force) {
                if (!Confirm::prompt('Website already exists. Do you want to override it? [y/n]', 'y', 'n')) {
                    // throw new \Exception(sprintf('Config file already exists: "%s".', $this->getPath().'/'.self::CONFIG_FILE));
                    exit(0);
                }
            }
            $root = __DIR__.'/../../';
            if (Plateform::isPhar()) {
                $root = Plateform::getPharPath().'/';
            }
            $this->wlAnnonce('Creating a new website...');
            $this->fs->copy($root.'skeleton/phpoole.yml', $this->getPath().'/'.self::CONFIG_FILE, true);
            $this->fs->mirror($root.'skeleton/content', $this->getPath().'/content');
            $this->fs->mirror($root.'skeleton/layouts', $this->getPath().'/layouts');
            $this->fs->mirror($root.'skeleton/static', $this->getPath().'/static');
            $this->wlDone('Done!');
        } catch (\Exception $e) {
            throw new \Exception(sprintf($e->getMessage()));
        }
    }
}
