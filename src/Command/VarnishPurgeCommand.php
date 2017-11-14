<?php
declare(strict_types=1);

namespace BC\Purger\Command;


use BC\Purger\Command\Base\DomainCommand;
use BC\Purger\Exception\VarnishException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class VarnishPurgeCommand
 * @package BC\Purger\Command
 */
class VarnishPurgeCommand extends DomainCommand
{
    /**
     * @return void
     * @throws \Exception
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('varnish:purge')
            ->setDescription('Run purge command for Varnish cache.');

        $this
            ->addOption('force-all', 'a', InputOption::VALUE_NONE, 'Enforce the purging of all routes in the Varnish cache for each host.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $varnishHosts = $this->getVarnishHosts();
            $routesCollection = $this->getRouteCollection(true);
            $forceAll = false;

            if ($input->hasOption('force-all')) {
                $forceAll = $input->getOption('force-all');
            }

            $resultOutput = $this->executePurge($varnishHosts, $routesCollection, $forceAll);
            $output->writeln($resultOutput);
        } catch (\Exception $exception) {
            $output->writeln(sprintf('Varnish Error: %s', $exception->getMessage()));
        }
    }

    /**
     * @param array $varnishHosts
     * @param array $routesCollection
     * @param bool $forceAll
     * @return string
     * @throws \Exception
     */
    private function executePurge(array $varnishHosts, array $routesCollection, bool $forceAll)
    {
        if (empty($routesCollection) && !$forceAll) {
            throw new RuntimeException('Nothing to do. Set "--force-all" if you want to purge without specific routes.');
        }

        $purgeOutput = '';

        foreach ($varnishHosts as $varnishHost) {
            if ($forceAll) {
                $purgeOutput .= $this->executeSinglePurgeByPattern($varnishHost, '.*');
            }

            $purgeOutput .= $this->executeRoutesPurge($varnishHost, $routesCollection);
        }

        return $purgeOutput;
    }

    /**
     * @param $varnishHost
     * @param array $routesCollection
     * @return string
     * @throws \Exception
     */
    private function executeRoutesPurge($varnishHost, array $routesCollection)
    {
        $purgeOutput = '';

        foreach ($routesCollection as $route) {
            $purgeOutput .= $this->executeSinglePurgeByPattern($varnishHost, $route);
        }

        return $purgeOutput;
    }

    /**
     * @param $varnishHost
     * @param $urlPattern
     * @return string
     * @throws \Exception
     */
    private function executeSinglePurgeByPattern($varnishHost, $urlPattern)
    {
        $hostPattern = str_replace('.', '\.', $this->domain);
        $this->executeVarnishPurge($varnishHost, $hostPattern, $urlPattern);
        return sprintf('Add purge on Varnish Server: "%s" for host: "%s" and route: "%s" ' . "\n", $varnishHost, $hostPattern, $urlPattern);
    }

    /**
     * @param $varnishHost
     * @param $hostPattern
     * @param $urlPattern
     * @throws VarnishException
     */
    private function executeVarnishPurge($varnishHost, $hostPattern, $urlPattern)
    {
        $curlHandler = curl_init();

        curl_setopt_array($curlHandler, [
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_CUSTOMREQUEST => 'BAN',
            CURLOPT_URL => $varnishHost,
            CURLOPT_HTTPHEADER => [
                sprintf('X-Ban-Url: ^%s', $urlPattern),
                sprintf('X-Ban-Host: %s', $hostPattern),
            ]
        ]);

        curl_exec($curlHandler);

        $curlStatus = curl_getinfo($curlHandler, CURLINFO_RESPONSE_CODE);

        curl_close($curlHandler);

        if ($curlStatus !== 200) {
            throw new VarnishException(sprintf('Unable to purge Varnish Server: "%s". Your Server might not have access permissions.', $varnishHost));
        }
    }
}
