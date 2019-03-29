<?php
/*
 * Copyright (c) Arnaud Ligny <arnaud@ligny.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cecil\Renderer;

use Cecil\Collection\Page\Page;
use Cecil\Collection\Page\Type as PageType;
use Cecil\Config;
use Cecil\Exception\Exception;
use Cecil\Util;

/**
 * Class Layout.
 */
class Layout
{
    /**
     * Layout file finder.
     *
     * @param Page   $page
     * @param string $format
     * @param Config $config
     *
     * @throws Exception
     *
     * @return string
     */
    public static function finder(Page $page, string $format, Config $config)
    {
        $layout = 'unknown';

        // what layouts, in what format, could be use for the page?
        $layouts = self::fallback($page, $format);

        // take the first available layout
        foreach ($layouts as $layout) {
            // is it in layouts/ dir?
            if (Util::getFS()->exists($config->getLayoutsPath().'/'.$layout)) {
                return $layout;
            }
            // is it in <theme>/layouts/ dir?
            if ($config->hasTheme()) {
                $themes = $config->getTheme();
                foreach ($themes as $theme) {
                    if (Util::getFS()->exists($config->getThemeDirPath($theme, 'layouts').'/'.$layout)) {
                        return $layout;
                    }
                }
            }
            // is it in res/layouts/ dir?
            if (Util::getFS()->exists($config->getInternalLayoutsPath().'/'.$layout)) {
                return $layout;
            }
        }

        throw new Exception(sprintf("Layout '%s' not found for page '%s'!", $layout, $page->getId()));
    }

    /**
     * Layout fall-back.
     *
     * @param Page   $page
     * @param string $format
     *
     * @return string[]
     *
     * @see finder()
     */
    protected static function fallback(Page $page, string $format)
    {
        // remove redundant '.twig' extension
        $layout = str_replace('.twig', '', $page->getVariable('layout'));

        switch ($page->getType()) {
            case PageType::HOMEPAGE:
                $layouts = [
                    "index.$format.twig",
                    "_default/list.$format.twig",
                    "_default/page.$format.twig",
                ];
                break;
            case PageType::SECTION:
                $layouts = [
                    // "section/$section.$format.twig",
                    "_default/section.$format.twig",
                    "_default/list.$format.twig",
                ];
                if ($page->getPath()) {
                    $section = explode('/', $page->getPath())[0];
                    $layouts = array_merge(
                        [sprintf('section/%s.%s.twig', $section, $format)],
                        $layouts
                    );
                }
                break;
            case PageType::TAXONOMY_VOCABULARY:
                $layouts = [
                    // "taxonomy/$singular.$format.twig",
                    "_default/taxonomy.$format.twig",
                    "_default/list.$format.twig",
                ];
                if ($page->getVariable('singular')) {
                    $layouts = array_merge(
                        [sprintf('taxonomy/%s.%s.twig', $page->getVariable('singular'), $format)],
                        $layouts
                    );
                }
                break;
            case PageType::TAXONOMY_TERMS:
                $layouts = [
                    // "taxonomy/$singular.terms.$format.twig",
                    "_default/terms.$format.twig",
                ];
                if ($page->getVariable('singular')) {
                    $layouts = array_merge(
                        [sprintf('taxonomy/%s.terms.%s.twig', $page->getVariable('singular'), $format)],
                        $layouts
                    );
                }
                break;
            default:
                $layouts = [
                    // "$section/$layout.$format.twig",
                    // "$layout.$format.twig",
                    // "$section/page.$format.twig",
                    // "page.$format.twig",
                    "_default/page.$format.twig",
                ];
                $layouts = array_merge(
                    ["page.$format.twig"],
                    $layouts
                );
                if ($page->getSection()) {
                    $layouts = array_merge(
                        [sprintf('%s/page.%s.twig', $page->getSection(), $format)],
                        $layouts
                    );
                }
                if ($page->getVariable('layout')) {
                    $layouts = array_merge(
                        [sprintf('%s.%s.twig', $layout, $format)],
                        $layouts
                    );
                    if ($page->getSection()) {
                        $layouts = array_merge(
                            [sprintf('%s/%s.%s.twig', $page->getSection(), $layout, $format)],
                            $layouts
                        );
                    }
                }
        }

        return $layouts;
    }
}
