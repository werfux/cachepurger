<?php
declare(strict_types=1);

namespace BC\Purger\Command\Base;

use BC\Purger\Helper\SourceFileHelper;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DomainCommand
 * @package BC\Purger\Command\Base
 */
class DomainCommand extends Command
{
    /** @var  string */
    protected $domain;

    /**
     * @return void
     * @throws \Exception
     */
    public function configure()
    {
        parent::configure();

        $this
            ->addOption('domain', 'd', InputOption::VALUE_REQUIRED, 'Set the domain you want to purge.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->domain = $this->getDomain($input);
    }

    /**
     * @param InputInterface $input
     * @return string
     * @throws \Exception
     */
    private function getDomain(InputInterface $input)
    {
        if (!$input->hasOption('domain')) {
            throw new RuntimeException('Required option "domain" not configured.');
        }

        $loadedDomain = $input->getOption('domain') ?? SourceFileHelper::loadDomainFromFile($this->sourceFile);

        if (empty($loadedDomain)) {
            throw new RuntimeException('You have to define a domain for your pruge request.');
        }

        if (!filter_var($loadedDomain, FILTER_VALIDATE_DOMAIN)) {
            throw new InvalidOptionException('The given value for "domain" does not validate.');
        }

        return $loadedDomain;
    }
}
