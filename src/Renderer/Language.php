<?php
/**
 * This file is part of the Cecil/Cecil package.
 *
 * Copyright (c) Arnaud Ligny <arnaud@ligny.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cecil\Renderer;

use Cecil\Config;
use Cecil\Exception\Exception;

/**
 * Class Language.
 */
class Language
{
    /** @var Config */
    protected $config;
    /** @var string Current language. */
    protected $language;

    /**
     * @param Config      $config
     * @param string|null $language
     */
    public function __construct(Config $config, string $language = null)
    {
        $this->config = $config;
        $this->language = $language;
    }

    /**
     * Returns the current language.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->language) {
            return $this->language;
        }

        return $this->config->getLanguageDefault();
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        if ($this->hasProperty('name')) {
            return $this->config->getLanguageProperty('name', $this->language);
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getLocale(): ?string
    {
        if ($this->hasProperty('locale')) {
            return $this->config->getLanguageProperty('locale', $this->language);
        }

        return null;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        if ($this->language) {
            return $this->config->getLanguageIndex($this->language);
        }

        return 0;
    }

    /**
     * @return bool
     */
    private function hasProperty(string $property): bool
    {
        $value = $this->config->getLanguageProperty($property, $this->language);

        if (empty($value)) {
            $language = $this->language ?: $this->config->getLanguageDefault();

            throw new Exception(sprintf(
                'The property "%s" is empty for language "%s".',
                $property,
                $language
            ));
        }

        return true;
    }
}
