<?php
/*
 * Copyright (c) Arnaud Ligny <arnaud@ligny.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cecil\Collection\Page;

/**
 * Class FileStr.
 */
class FileStr
{
    // https://regex101.com/r/tJWUrd/6
    // ie: "blog/2017-10-19-post-1.md" prefix is "2017-10-19"
    // ie: "projet/1-projet-a.md" prefix is "1"
    const PREFIX_PATTERN = '^(|.*\/)(([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])|[0-9]+)(-|_|\.)(.*)$';
    // https://regex101.com/r/GlgBdT/7
    // ie: "blog/2017-10-19-post-1.en.md" suffix is "en"
    // ie: "projet/1-projet-a.fr-FR.md" suffix is "fr-FR"
    const SUFFIX_PATTERN = '^(.*)\.([a-z]{2}(-[A-Z]{2})?)$';

    /**
     * Return true if the string contains a prefix or a suffix.
     *
     * @param string $string
     * @param string $type
     *
     * @return bool
     */
    protected static function has(string $string, string $type): bool
    {
        if (preg_match('/'.self::getPattern($type).'/', $string)) {
            return true;
        }

        return false;
    }

    /**
     * Return true if the string contains a prefix.
     *
     * @param string $string
     *
     * @return bool
     */
    public static function hasPrefix(string $string): bool
    {
        return self::has($string, 'prefix');
    }

    /**
     * Return true if the string contains a suffix.
     *
     * @param string $string
     *
     * @return bool
     */
    public static function hasSuffix(string $string): bool
    {
        return self::has($string, 'suffix');
    }

    /**
     * Return the prefix or the suffix if exists.
     *
     * @param string $string
     * @param string $type
     *
     * @return string[]|null
     */
    protected static function get(string $string, string $type): ?string
    {
        if (self::has($string, $type)) {
            preg_match('/'.self::getPattern($type).'/', $string, $matches);
            switch ($type) {
                case 'prefix':
                    return $matches[2];
                    break;
                case 'suffix':
                    return $matches[2];
            }
        }

        return null;
    }

    /**
     * Return the prefix if exists.
     *
     * @param string $string
     *
     * @return string[]|null
     */
    public static function getPrefix(string $string): ?string
    {
        return self::get($string, 'prefix');
    }

    /**
     * Return the suffix if exists.
     *
     * @param string $string
     *
     * @return string[]|null
     */
    public static function getSuffix(string $string): ?string
    {
        return self::get($string, 'suffix');
    }

    /**
     * Return string without the prefix and the suffix (if exists).
     *
     * @param string $string
     *
     * @return string
     */
    public static function sub(string $string): string
    {
        if (self::hasPrefix($string)) {
            preg_match('/'.self::getPattern('prefix').'/', $string, $matches);

            $string = $matches[1].$matches[7];
        }
        if (self::hasSuffix($string)) {
            preg_match('/'.self::getPattern('suffix').'/', $string, $matches);

            $string = $matches[1];
        }

        return $string;
    }

    /**
     * Return string without the prefix (if exists).
     *
     * @param string $string
     *
     * @return string
     */
    public static function subPrefix(string $string): string
    {
        if (self::hasPrefix($string)) {
            preg_match('/'.self::getPattern('prefix').'/', $string, $matches);

            return $matches[1].$matches[7];
        }

        return $string;
    }

    /**
     * Return expreg pattern by $type.
     *
     * @param string $type
     *
     * @return string
     */
    protected static function getPattern(string $type): string
    {
        switch ($type) {
            case 'prefix':
                return self::PREFIX_PATTERN;
                break;
            case 'suffix':
                return self::SUFFIX_PATTERN;
                break;
            default:
                throw new Exception(\sprintf('%s must be "prefix" or "suffix"', $type));
        }
    }
}
