<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Eloquent\Composer\Configuration\ConfigurationReader;
use BC\Purger\Helper\RequirementsHelper;
use BC\Purger\Command\AkamaiPurgeCommand;
use BC\Purger\Command\AkamaiCredentialsCommand;
use BC\Purger\Command\VarnishPurgeCommand;
use BC\Purger\Command\RedisPurgeCommand;
use Symfony\Component\Console\Application;

// Requirements Check
try {
    RequirementsHelper::checkMinimumPHPVersion();
    RequirementsHelper::checkRequiredPHPExtensions();
} catch (\Exception $exception) {
    printf($exception->getMessage() . "\n");
    die();
}

// Read Meta Information
$reader = new ConfigurationReader();
$metaInformation = $reader->read(__DIR__ . '/composer.json');

// Application
$consoleApplication = new Application();

// Bootstrap Application
$consoleApplication->setName($metaInformation->name());
$consoleApplication->setVersion(sprintf('v%s', $metaInformation->version()));

// Register Commands
$consoleApplication->add(new AkamaiPurgeCommand());
$consoleApplication->add(new AkamaiCredentialsCommand());
$consoleApplication->add(new VarnishPurgeCommand());
$consoleApplication->add(new RedisPurgeCommand());

// Run Application
$consoleApplication->run();
