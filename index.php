<?php

require_once __DIR__ . '/vendor/autoload.php';

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

// Application
$consoleApplication = new Application();

// Bootstrap Application
$consoleApplication->setName('Cache Purger');
$consoleApplication->setVersion('v0.0.1');

// Register Commands
$consoleApplication->add(new AkamaiPurgeCommand());
$consoleApplication->add(new AkamaiCredentialsCommand());
$consoleApplication->add(new VarnishPurgeCommand());
$consoleApplication->add(new RedisPurgeCommand());

// Run Application
$consoleApplication->run();
