<?php
/*
 * This file is part of the Nautilus package.
 *
 * (c) Leo <leo.on.the.earth@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nautilus\Console;

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console Logger Helper
 */
class Logger
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var FormatterHelper
     */
    private $formatterHelper;

    /**
     * ConsoleLogger constructor.
     *
     * @param OutputInterface $output
     * @param FormatterHelper $formatterHelper
     */
    public function __construct(OutputInterface $output, FormatterHelper $formatterHelper)
    {
        $this->output = $output;
        $this->formatterHelper = $formatterHelper;
    }

    /**
     * Output normal log messages (without style)
     *
     * @param string|string[] $messages
     */
    public function normal($messages)
    {
        $this->log($messages, '');
    }

    /**
     * Output success log messages
     *
     * @param string|string[] $messages
     */
    public function success($messages)
    {
        $this->log($messages, 'fg=black;bg=green');
    }

    /**
     * Output error log messages
     *
     * @param string|string[] $messages
     */
    public function error($messages)
    {
        $this->log($messages, 'error');
    }

    /**
     * Output log messages
     *
     * @param string|string[] $messages
     * @param string          $style
     */
    public function log($messages, $style)
    {
        if (empty($style)) {
            if (!\is_array($messages)) {
                $messages = array($messages);
            }

            $message = implode("\n", $messages);
        } else {
            $message = $this->formatterHelper->formatBlock($messages, $style, true);
        }

        $this->output->writeln('');
        $this->output->writeln($message);
        $this->output->writeln('');
    }
}
