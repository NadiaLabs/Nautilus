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
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for generate documents
 */
class GenerateCommand extends Command
{
    /**
     * Nautilus config filename
     *
     * @var string
     */
    const CONFIG_FILENAME = 'nautilus.json';
    /**
     * Default theme name
     *
     * @var string
     */
    const DEFAULT_THEME_NAME = 'default';

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
        $cwd = getcwd();
        $config = $this->loadConfiguration();
        $globalParameters = $config['parameters'];
        $markdownOptions = array(
            Markdown::ENABLE_PHP_EVAL => $input->getOption('enable-php-eval'),
            Markdown::WORKING_DIR => $cwd,
        );
        $generatedFilePaths = array();

        foreach ($config['documents'] as $documentConfig) {
            $generatedFilePaths[] = $this->writeDocument($documentConfig, $markdownOptions, $globalParameters);
        }

        $this->getLogger($output)->success(array_merge(array('Generated files:'), $generatedFilePaths));
    }

    /**
     * Load Nautilus configuration
     *
     * @return array
     */
    private function loadConfiguration()
    {
        $configFilePath = $this->getConfigFilePath();

        if (!file_exists($configFilePath)) {
            throw new InvalidArgumentException('nautilus.json not found!');
        }

        $config = file_get_contents($configFilePath);
        $config = trim($config);
        $config = empty($config) ? array() : @json_decode($config, true);
        $config = !is_array($config) ? array() : $config;

        $this->checkAndFixConfiguration($config);

        return $config;
    }

    /**
     * Check and fix configuration format
     *
     * @param array $config
     */
    private function checkAndFixConfiguration(array &$config)
    {
        if (empty($config['documents'])) {
            throw new InvalidArgumentException(
                '"documents" is required in "'.static::CONFIG_FILENAME.'"!'
            );
        }
        if (!is_array($config['documents'])) {
            throw new InvalidArgumentException(
                '"documents" should be an array in "'.static::CONFIG_FILENAME.'"!'
            );
        }

        foreach ($config['documents'] as &$document) {
            if (empty($document['title'])) {
                throw new InvalidArgumentException(
                    '"documents.title" is required in "'.static::CONFIG_FILENAME.'"!'
                );
            }
            if (empty($document['outputFilePath'])) {
                throw new InvalidArgumentException(
                    '"documents.outputFilePath" is required in "'.static::CONFIG_FILENAME.'"!'
                );
            }

            if (empty($document['theme'])) {
                $document['theme'] = static::DEFAULT_THEME_NAME;
            }
            if (empty($document['parameters'])) {
                $document['parameters'] = array();
            }
            if (empty($document['markdownOptions'])) {
                $document['markdownOptions'] = array();
            }

            if (empty($document['chapters'])) {
                throw new InvalidArgumentException(
                    '"documents.chapters" is required in "'.static::CONFIG_FILENAME.'"!'
                );
            }
            if (!is_array($document['chapters'])) {
                throw new InvalidArgumentException(
                    '"documents.chapters" should be an array in "'.static::CONFIG_FILENAME.'"!'
                );
            }

            foreach ($document['chapters'] as &$chapter) {
                if (empty($chapter['title'])) {
                    throw new InvalidArgumentException(
                        '"documents.chapters.title" is required in "'.static::CONFIG_FILENAME.'"!'
                    );
                }
                if (empty($chapter['filePath'])) {
                    throw new InvalidArgumentException(
                        '"documents.chapters.filePath" is required in "'.static::CONFIG_FILENAME.'"!'
                    );
                }
                if (empty($chapter['parameters'])) {
                    $chapter['parameters'] = array();
                }
            }
        }

        if (empty($config['parameters'])) {
            $config['parameters'] = array();
        }
    }

    /**
     * @return string
     */
    private function getConfigFilePath()
    {
        return getcwd().'/'.static::CONFIG_FILENAME;
    }

    /**
     * Write document content to a file
     *
     * @param array $config                 Document configuration
     * @param array $defaultMarkdownOptions Default markdown parser options
     * @param array $globalParameters       Global parameters
     *
     * @return string Output file path
     */
    private function writeDocument(array $config, array $defaultMarkdownOptions, array $globalParameters = array())
    {
        $parameters = array_merge($globalParameters, $config['parameters']);
        $markdownOptions = array_merge($defaultMarkdownOptions, $config['markdownOptions']);
        $cwd = $markdownOptions[Markdown::WORKING_DIR];
        $markdown = new Markdown($markdownOptions);
        $posts = array();

        foreach ($config['chapters'] as $index => $chapterConfig) {
            $chapterParameters = array_merge($parameters, $chapterConfig['parameters']);
            $filePath = $cwd.'/'.trim($chapterConfig['filePath'], '/ ');

            if (!file_exists($filePath)) {
                throw new InvalidArgumentException('Markdown file not found! ("'.$chapterConfig['filePath'].'")');
            }

            $posts[] = $markdown->transform2($index, file_get_contents($filePath), $chapterConfig, $chapterParameters);
        }

        $themeFilePath = $this->getThemeFilePath($config);
        $html = $this->getPhpRenderer()->render($themeFilePath, array(
            'documentConfig' => $config,
            'posts' => $posts,
            'parameters' => $parameters,
        ));
        $outputFilePath = $cwd.'/'.trim($config['outputFilePath'], '/ ');

        file_put_contents($outputFilePath, $html);

        return $outputFilePath;
    }

    /**
     * @param array $documentConfig
     *
     * @return string
     */
    private function getThemeFilePath(array $documentConfig)
    {
        if (!empty($documentConfig['theme'])) {
            if (static::DEFAULT_THEME_NAME === $documentConfig['theme']) {
                return __DIR__.'/../../themes/'.static::DEFAULT_THEME_NAME.'/index.php';
            }

            if (file_exists($documentConfig['theme'])) {
                return $documentConfig['theme'];
            }
        }

        return '';
    }

    /**
     * @return PhpRenderer
     */
    private function getPhpRenderer()
    {
        static $renderer = null;

        if (!$renderer instanceof PhpRenderer) {
            $renderer = new PhpRenderer();
        }

        return $renderer;
    }

    /**
     * @param OutputInterface $output
     *
     * @return Logger
     */
    private function getLogger(OutputInterface $output)
    {
        static $logger = null;

        if (!$logger instanceof Logger) {
            $logger = new Logger($output, $this->getHelper('formatter'));
        }

        return $logger;
    }
}
