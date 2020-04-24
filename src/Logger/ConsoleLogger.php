<?php
/**
 * This file is part of the Cecil/Cecil package.
 *
 * Copyright (c) Arnaud Ligny <arnaud@ligny.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cecil\Logger;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Logger\ConsoleLogger as SfConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLogger extends SfConsoleLogger
{
    const ERROR = 'error';
    const WARNING = 'comment';
    const NOTICE = 'info';
    const INFO = 'text';

    private $output;
    private $verbosityLevelMap = [
        LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ALERT     => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::CRITICAL  => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ERROR     => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::WARNING   => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::NOTICE    => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::INFO      => OutputInterface::VERBOSITY_VERBOSE,
        LogLevel::DEBUG     => OutputInterface::VERBOSITY_DEBUG,
    ];
    private $formatLevelMap = [
        LogLevel::EMERGENCY => self::ERROR,
        LogLevel::ALERT     => self::ERROR,
        LogLevel::CRITICAL  => self::ERROR,
        LogLevel::ERROR     => self::ERROR,
        LogLevel::WARNING   => self::INFO,
        LogLevel::NOTICE    => self::NOTICE,
        LogLevel::INFO      => self::INFO,
        LogLevel::DEBUG     => self::INFO,
    ];

    /** @var ProgressBar */
    protected $progressBar = null;
    /** @var int */
    private $progressBarMax;

    public function __construct(OutputInterface $output, array $verbosityLevelMap = [], array $formatLevelMap = [])
    {
        $this->output = $output;
        $this->verbosityLevelMap = $verbosityLevelMap + $this->verbosityLevelMap;
        $this->formatLevelMap = $formatLevelMap + $this->formatLevelMap;
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        if (!isset($this->verbosityLevelMap[$level])) {
            throw new InvalidArgumentException(sprintf('The log level "%s" does not exist.', $level));
        }

        $output = $this->output;

        if ($output->getVerbosity() >= $this->verbosityLevelMap[$level]) {
            $outputStyle = new OutputFormatterStyle('white');
            $output->getFormatter()->setStyle('text', $outputStyle);

            if ($output->getVerbosity() == OutputInterface::VERBOSITY_NORMAL && array_key_exists('step', $context)) {
                $this->printProgressBar($context['step'][0], $context['step'][1]);

                return;
            }

            $pattern = '<%1$s>%3$s</%1$s>';
            if (array_key_exists('progress', $context)) {
                $pattern = '<%1$s>('.$context['progress'][0].'/'.$context['progress'][1].') %3$s</%1$s>';

                if ($output->getVerbosity() == OutputInterface::VERBOSITY_VERBOSE) {
                    $this->printProgressBar($context['progress'][0], $context['progress'][1]);

                    return;
                }
            }

            $output->writeln(
                sprintf($pattern, $this->formatLevelMap[$level], $level, $this->interpolate($message, $context)),
                $this->verbosityLevelMap[$level]
            );
        }
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @author PHP Framework Interoperability Group
     */
    private function interpolate(string $message, array $context): string
    {
        if (false === strpos($message, '{')) {
            return $message;
        }

        $replacements = [];
        foreach ($context as $key => $val) {
            if (null === $val || is_scalar($val) || (\is_object($val) && method_exists($val, '__toString'))) {
                $replacements["{{$key}}"] = $val;
            } elseif ($val instanceof \DateTimeInterface) {
                $replacements["{{$key}}"] = $val->format(\DateTime::RFC3339);
            } elseif (\is_object($val)) {
                $replacements["{{$key}}"] = '[object '.\get_class($val).']';
            } else {
                $replacements["{{$key}}"] = '['.\gettype($val).']';
            }
        }

        return strtr($message, $replacements);
    }

    /**
     * Prints the Progress Bar.
     *
     * @param int $itemsCount
     * @param int $itemsMax
     *
     * @return void
     */
    protected function printProgressBar(int $itemsCount, int $itemsMax): void
    {
        $this->createProgressBar($itemsMax);
        $this->progressBar->clear();
        $this->progressBar->setProgress($itemsCount);
        $this->progressBar->display();
        if ($itemsCount == $itemsMax) {
            $this->progressBar->finish();
            $this->output->writeln('');
        }
    }

    /**
     * Creates the Progress bar.
     *
     * @param int $max
     *
     * @return void
     */
    private function createProgressBar(int $max): void
    {
        if ($this->progressBar === null || $max != $this->progressBarMax) {
            $this->progressBarMax = $max;
            $this->progressBar = new ProgressBar($this->output, $max);
            $this->progressBar->setOverwrite(true);
            $this->progressBar->setFormat('%percent:3s%% [%bar%] %current%/%max%');
            $this->progressBar->setBarCharacter('#');
            $this->progressBar->setEmptyBarCharacter(' ');
            $this->progressBar->setProgressCharacter('#');
            $this->progressBar->setRedrawFrequency(1);
            $this->progressBar->start();
        }
    }
}
