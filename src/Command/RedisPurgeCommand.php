<?php
declare(strict_types=1);

namespace BC\Purger\Command;

use BC\Purger\Command\Base\Command;
use BC\Purger\Model\RedisConnection;
use BC\Purger\Service\RedisService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RedisPurgeCommand
 * @package BC\Purger\Command
 */
class RedisPurgeCommand extends Command
{
    // redis-cli -h stg-bestcheck-redis-ber-01.int.chip.de flushall
    /**
     * @return void
     * @throws \Exception
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('redis:purge')
            ->setDescription('Run flush commands for Redis cache.');


        $this
            ->addOption('pattern', 'p', InputOption::VALUE_REQUIRED, 'Set a pattern that is used to delete matching keys.')
            ->addOption('database', 'db', InputOption::VALUE_REQUIRED, 'Define the database you want to clean up.')
            ->addOption('force-all', 'a', InputOption::VALUE_NONE, 'Enforce the purging of all records on Redis server for each host.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $forceAll = false;
            $redisDatabase = null;
            $redisPattern = null;

            if ($input->hasOption('force-all')) {
                $forceAll = $input->getOption('force-all');
            }

            if ($input->hasOption('database')) {
                $redisDatabase = $input->getOption('database');
            }

            if ($input->hasOption('pattern')) {
                $redisPattern = $input->getOption('pattern');
            }

            $resultOutput = $this->executePurge($forceAll, $redisDatabase, $redisPattern);
            $output->writeln($resultOutput);
        } catch (\Exception $exception) {
            $output->writeln(sprintf('Redis Error: %s', $exception->getMessage()));
        }
    }

    /**
     * @param $forceAll
     * @param $redisDatabase
     * @param $redisPattern
     * @throws \Exception
     */
    private function executePurge($forceAll, $redisDatabase, $redisPattern)
    {
        $redisConnections = $this->getRedisConnections();

        foreach ($redisConnections as $redisConnectionData) {
            $redisConnection = new RedisConnection($redisConnectionData);
            $redisService = new RedisService($redisConnection);

            if ($forceAll) {
                $redisService->flushAll();
                continue;
            }

            if ($redisDatabase) {
                $redisService->flushDatabase($redisDatabase);
                continue;
            }

            if ($redisPattern) {
                $redisService->flushKeysByPattern($redisPattern);
                continue;
            }
        }
    }
}
