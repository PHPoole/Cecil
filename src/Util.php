<?php
/**
 * This file is part of the Cecil/Cecil package.
 *
 * Copyright (c) Arnaud Ligny <arnaud@ligny.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cecil;

class Util
{
    /**
     * Formats a class name.
     *
     * ie: "Cecil\Step\PostProcessHtml" become "PostProcessHtml"
     *
     * @param object $class
     * @param array  $options
     *
     * @return string
     */
    public static function formatClassName($class, array $options = []): string
    {
        $lowercase = false;
        extract($options, EXTR_IF_EXISTS);

        $className = substr(strrchr(get_class($class), '\\'), 1);
        if ($lowercase) {
            $className = strtolower($className);
        }

        return $className;
    }

    /**
     * Converts an array of strings into a path.
     *
     * @param string[] $path
     *
     * @return string
     */
    public static function joinPath(string ...$path): string
    {
        array_walk($path, function (&$value, $key) {
            $value = str_replace('\\', '/', $value);
            $value = rtrim($value, '/');
            $value = $key == 0 ? $value : ltrim($value, '/');
        });

        return implode('/', $path);
    }

    /**
     * Converts an array of strings into a system path.
     *
     * @param string[] $path
     *
     * @return string
     */
    public static function joinFile(string ...$path): string
    {
        array_walk($path, function (&$value, $key) use (&$path) {
            $value = str_replace(['\\', '/'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $value);
            $value = rtrim($value, DIRECTORY_SEPARATOR);
            $value = $key == 0 ? $value : ltrim($value, DIRECTORY_SEPARATOR);
            // unset entry with empty value
            if (empty($value)) {
                unset($path[$key]);
            }
        });

        return implode(DIRECTORY_SEPARATOR, $path);
    }
}
