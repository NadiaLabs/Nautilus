<?php
/*
 * This file is part of the Nautilus package.
 *
 * (c) Leo <leo.on.the.earth@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nautilus\Command;

use Nautilus\Console\Logger;
use Nautilus\Markdown\Markdown;
use Nautilus\Markdown\PhpRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for generate documents
 */
class GenerateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('generate')
            ->setDescription('Generate documents')
            ->addOption('enable-php-eval', 'e', InputOption::VALUE_NONE, 'Enable PHP eval function')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getLogger($output);
        $dir = getcwd();
        $configFilePath = $dir.'/nautilus.json';

        if (!file_exists($configFilePath)) {
            $logger->error('nautilus.json not found!');
            return;
        }

        $config = file_get_contents($dir.'/nautilus.json');
        $config = trim($config);
        $config = empty($config) ? false : @json_decode($config, true);

        if (empty($config)) {
            $logger->error('nautilus.json format invalid!');
            return;
        }

        $globalParameters = $config['parameters'];
        $markdownOptions = array(
            Markdown::ENABLE_PHP_EVAL => $input->getOption('enable-php-eval'),
            Markdown::WORKING_DIR => $dir,
        );
        $phpRenderer = new PhpRenderer();
        $generatedFilePaths = array();

        foreach ($config['entries'] as $entry) {
            $entryParameters = array_merge($globalParameters, $entry['parameters']);
            $entryMarkdownOptions = array_merge($markdownOptions, $entry['markdownOptions']);
            $markdown = new Markdown($entryMarkdownOptions);
            $posts = array();

            foreach ($entry['documents'] as $index => $document) {
                $documentParameters = array_merge($entryParameters, $document['parameters']);
                $filePath = $dir.'/'.trim($document['filePath'], '/ ');

                if (!file_exists($filePath)) {
                    $logger->error('Markdown file not found! ("'.$document['filePath'].'")');
                    return;
                }

                $posts[] = $markdown->transform2($index, file_get_contents($filePath), $document, $documentParameters);
            }

            $theme = $this->getThemeFilePath($entry);
            $html = $phpRenderer->render($theme, array(
                'entry' => $entry,
                'posts' => $posts,
                'parameters' => $entryParameters
            ));
            $generatedFilePaths[] = $outputFilePath = $dir.'/'.trim($entry['outputFilePath'], '/ ');

            file_put_contents($outputFilePath, $html);
        }

        $messages = array_merge(array('Generated files:'), $generatedFilePaths);
        $this->getLogger($output)->success($messages);
    }

    /**
     * @param array $entry
     *
     * @return string
     */
    protected function getThemeFilePath(array $entry)
    {
        if (!empty($entry['theme'])) {
            if ('default' === $entry['theme']) {
                return __DIR__.'/../../themes/default/index.php';
            }

            if (file_exists($entry['theme'])) {
                return $entry['theme'];
            }
        }

        return '';
    }

    /**
     * @param OutputInterface $output
     *
     * @return Logger
     */
    protected function getLogger(OutputInterface $output)
    {
        static $logger = null;

        if (!$logger instanceof Logger) {
            $logger = new Logger($output, $this->getHelper('formatter'));
        }

        return $logger;
    }
}
