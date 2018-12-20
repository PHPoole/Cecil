<?php
/*
 * This file is part of the Cecil/Cecil package.
 *
 * Copyright (c) Arnaud Ligny <arnaud@ligny.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// CLI routes
return [
    [
        'name'    => 'newsite',
        'route'   => '[<path>] [--force|-f]',
        'aliases' => [
            'f' => 'force',
        ],
        'short_description'    => 'Create a new website',
        'description'          => 'Create a new website in the current directory, or in <path> if provided.',
        'options_descriptions' => [
            '<path>'     => 'Website\'s path.',
            '--force|-f' => 'Override the directory if already exist.',
        ],
        'defaults' => [
            'path' => getcwd(),
        ],
        'handler' => 'Cecil\Command\NewWebsite',
    ],
    [
        'name'    => 'new',
        'route'   => '<name> [<path>] [--force|-f]',
        'aliases' => [
            'f' => 'force',
        ],
        'short_description'    => 'Create a new content',
        'description'          => 'Create a new content file (with a default title and current date).',
        'options_descriptions' => [
            '<name>'     => 'Page name.',
            '<path>'     => 'Website\'s path.',
            '--force|-f' => 'Override the file if already exist.',
        ],
        'defaults' => [
            'path' => getcwd(),
        ],
        'handler' => 'Cecil\Command\NewPage',
    ],
    [
        'name'    => 'build',
        'route'   => '[<path>] [--drafts|-d] [--verbose|-v] [--quiet|-q] [--baseurl=] [--destination=] [--dry-run]',
        'aliases' => [
            'd' => 'drafts',
            'v' => 'verbose',
            'q' => 'quiet',
        ],
        'short_description'    => 'Build the website',
        'description'          => 'Build the website in the output directory.',
        'options_descriptions' => [
            '<path>'        => 'Website\'s path',
            '--drafts|-d'   => 'Include drafts',
            '--verbose|-v'  => 'Print build details',
            '--quiet|-q'    => 'Disable build messages',
            '--baseurl'     => 'Set the base URL',
            '--destination' => 'Set the output directory',
            '--dry-run'     => 'Build without saving',
        ],
        'defaults' => [
            'path' => getcwd(),
        ],
        'handler' => 'Cecil\Command\Build',
    ],
    [
        'name'    => 'serve',
        'route'   => '[<path>] [--drafts|-d] [--open|-o]',
        'aliases' => [
            'd' => 'drafts',
            'o' => 'open',
        ],
        'short_description'    => 'Start the built-in server',
        'description'          => 'Start the live-reloading-built-in web server.',
        'options_descriptions' => [
            '<path>'      => 'Website\'s path',
            '--drafts|-d' => 'Include drafts',
            '--open|-o'   => 'Open browser automatically',
        ],
        'defaults' => [
            'path' => getcwd(),
        ],
        'handler' => 'Cecil\Command\Serve',
    ],
    [
        'name'                 => 'list',
        'route'                => '[<path>]',
        'short_description'    => 'List content',
        'description'          => 'List content tree.',
        'options_descriptions' => [
            '<path>' => 'Website\'s path',
        ],
        'defaults' => [
            'path' => getcwd(),
        ],
        'handler' => 'Cecil\Command\ListContent',
    ],
    [
        'name'                 => 'config',
        'route'                => '[<path>]',
        'short_description'    => 'Print configuration',
        'description'          => 'Print website configuration.',
        'options_descriptions' => [
            '<path>' => 'Website\'s path',
        ],
        'defaults' => [
            'path' => getcwd(),
        ],
        'handler' => 'Cecil\Command\Config',
    ],
    [
        'name'                 => 'clean',
        'route'                => '[<path>]',
        'short_description'    => 'Remove the output directory',
        'description'          => 'Remove the output directory and the local server temporary files.',
        'options_descriptions' => [
            '<path>' => 'Website\'s path',
        ],
        'defaults' => [
            'path' => getcwd(),
        ],
        'handler' => 'Cecil\Command\Clean',
    ],
];
