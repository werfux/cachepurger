<?php

namespace BC\Purger\Command\Base;

use BC\Purger\Helper\RoutesHelper;
use BC\Purger\Helper\SourceFileHelper;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BaseCommand
 * @package BC\Purger\Command
 */
class Command extends SymfonyCommand
{
    /** @var string */
    protected $sourceFile;

    /**
     * @return void
     * @throws \Exception
     */
    public function configure()
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Defines the source file with all the configurations you need for a purge.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->sourceFile = $this->getSourceFilePath($input);
    }

    /**
     * @param InputInterface $input
     * @return string
     * @throws \Exception
     */
    private function getSourceFilePath(InputInterface $input)
    {
        if (!$input->hasOption('file')) {
            throw new RuntimeException('Required option "file" not configured.');
        }

        $filePath = $input->getOption('file') ?? './purge.yml';

        if (!file_exists($filePath)) {
            throw new RuntimeException('Cannot find source file. (Default: ./purge.yml)');
        }

        if (!pathinfo($filePath, PATHINFO_EXTENSION) === 'yml') {
            throw new RuntimeException('Source file have to be a yaml file. (*.yml) ');
        }

        return $filePath;
    }

    /**
     * @param bool $allowEmptyResponse
     * @return array
     * @throws \Exception
     */
    protected function getRouteCollection($allowEmptyResponse = false)
    {
        try {
            $rawRoutes = SourceFileHelper::loadRoutesFromFile($this->sourceFile);
            return RoutesHelper::buildValidRoutes($rawRoutes);
        } catch (\Exception $exception) {
            if (!$allowEmptyResponse) {
                throw $exception;
            }
        }

        return [];
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getVarnishHosts()
    {
        return SourceFileHelper::loadVarnishHostsFromFile($this->sourceFile);
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getRedisConnections()
    {
        return SourceFileHelper::loadRedisConnectionsFromFile($this->sourceFile);
    }
}
