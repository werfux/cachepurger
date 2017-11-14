<?php
declare(strict_types=1);

namespace BC\Purger\Command;

use BC\Purger\Command\Base\DomainCommand;
use BC\Purger\Helper\AkamaiHelper;
use BC\Purger\Service\AkamaiService;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FastPurgeCommand
 * @package BC\Purger\Command
 */
class AkamaiPurgeCommand extends DomainCommand
{
    /** @var AkamaiService */
    private $akamaiService;

    /** @var bool */
    private $hasValidCredentials = false;

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        if (!AkamaiHelper::validEdgeRcFileExists()) {
            $this->callCredentialsCommand($output);
        }

        try {
            $this->akamaiService = new AkamaiService();
            $this->hasValidCredentials = true;
        } catch (\Exception $exception) {
            $output->writeln(sprintf('ERROR: %s', $exception->getMessage()));
        }
    }

    /**
     * @param OutputInterface $output
     * @return bool
     * @throws \Exception
     */
    private function callCredentialsCommand(OutputInterface $output)
    {
        $output->writeln('ATTENTION: Because of missing or malforemd Akamai credentials, you have to renew them.');
        $akamaiCredentialCommand = $this->getApplication()->find('akamai:credentials');
        $akamaiCredentialCommand->run(new ArrayInput([]), $output);

        return AkamaiHelper::validEdgeRcFileExists();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('akamai:purge')
            ->setDescription('Run fast purge command for a set of routes.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->hasValidCredentials) {
            $output->writeln('Credentials are invalid.');
            return ;
        }

        try {
            $routesCollection = $this->getRouteCollection();
            $purgeResponse = $this->akamaiService->postPurgeRequest($this->domain, $routesCollection);
            $output->writeln(sprintf('Akamai Pruge ID: %s', $purgeResponse->purgeId));
            $output->writeln(sprintf('Estimated Seconds: %s', $purgeResponse->estimatedSeconds));
        } catch (\Exception $exception) {
            $output->writeln(sprintf('Akamai Error: %s', $exception->getMessage()));
        }
    }
}
