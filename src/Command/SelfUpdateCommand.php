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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for updating nautilus.phar
 */
class SelfUpdateCommand extends Command
{
    /**
     * @var array
     */
    private $updatedFileInfo = array();

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('self-update')
            ->setDescription('Updates nautilus.phar to the latest version.')
            ->addOption(
                'download-url-prefix',
                'u',
                InputOption::VALUE_OPTIONAL,
                'Download URL prefix',
                'https://raw.githubusercontent.com/NadiaLabs/Nautilus/master'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $localFilename = realpath($_SERVER['argv'][0]) ? $_SERVER['argv'][0] : '';
        $tmpDir = is_writable(dirname($localFilename)) ? dirname($localFilename) : '';

        if (!is_writable($tmpDir)) {
            throw new \RuntimeException(
                'Nautilus update failed: the "'.$tmpDir.
                '" directory used to download the temp file could not be written'
            );
        }

        $urlPrefix = rtrim($input->getOption('download-url-prefix'), '/ \t\n\r\0\x0B');
        $versionUrl = $urlPrefix.'/nautilus-version';
        $pharUrl = $urlPrefix.'/nautilus.phar';
        $newFilename = $tmpDir . '/' . basename($localFilename, '.phar').'-temp.phar';

        $newVersion = trim(file_get_contents($versionUrl));
        $currentVersion = $this->getApplication()->getVersion();
        $logger = $this->getLogger($output);

        if (version_compare($newVersion, $currentVersion) <= 0) {
            $logger->normal('Your nautilus version is already up-to-date!');
            return;
        }

        $logger->normal('Updating to version '.$newVersion.'......');

        file_put_contents($newFilename, file_get_contents($pharUrl));

        @chmod($newFilename, fileperms($localFilename));

        $this->updatedFileInfo = compact('newFilename', 'localFilename', 'newVersion');
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

    /**
     * @param OutputInterface $output
     */
    public function updateFile(OutputInterface $output)
    {
        if (empty($this->updatedFileInfo)) {
            return;
        }

        rename($this->updatedFileInfo['newFilename'], $this->updatedFileInfo['localFilename']);

        $this->updatedFileInfo = array();

        $this->getLogger($output)
            ->success('Updated to version '.$this->updatedFileInfo['newVersion'].' successfully!');
    }
}
