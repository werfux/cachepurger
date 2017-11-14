<?php
declare(strict_types=1);

namespace BC\Purger\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class AkamaiCredentialsCommand
 * @package BC\Purger\Command
 */
class AkamaiCredentialsCommand extends Command
{
    /** @var  QuestionHelper */
    private $questionHelper;

    /**
     * @return void
     * @throws \Exception
     */
    public function configure()
    {
        $this
            ->setName('akamai:credentials')
            ->setDescription('Setup the required .edgerc file for your Akamai connection.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->questionHelper = $this->getHelper('question');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $question = new ConfirmationQuestion('This will create a new or overwrite an existing ./edgerc file. Do want to continue? (y/n): ', false, '/^(y|j)/i');

        if ($this->questionHelper->ask($input, $output, $question)) {
            $this->executeCredentialsDialog($input, $output);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    private function executeCredentialsDialog(InputInterface $input, OutputInterface $output)
    {
        $clientSecret = $this->handleCredentialInput('client secret', $input, $output);
        $akamaiHost = $this->handleCredentialInput('akamai host', $input, $output);
        $accessToken = $this->handleCredentialInput('access token', $input, $output);
        $clientToken = $this->handleCredentialInput('client token', $input, $output);

        $this->writeCredentialsFile($clientSecret, $akamaiHost, $accessToken, $clientToken);
        $output->writeln('Credentials written to ./.edgrc file.');
    }

    /**
     * @param $credentialName
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     * @throws \Exception
     */
    private function handleCredentialInput($credentialName, InputInterface $input, OutputInterface $output)
    {
        $credentialQuestion = new Question(sprintf('Please enter your %s: ', $credentialName));

        $credentialQuestion->setValidator(function ($answer) use ($credentialName) {
            if (!is_string($answer) || empty($answer)) {
                throw new \RuntimeException(sprintf('Your %s must not be empty.', $credentialName));
            }

            return $answer;
        });

        $credentialQuestion->setMaxAttempts(2);

        return $this->questionHelper->ask($input, $output, $credentialQuestion);
    }

    /**
     * @param $clientSecret
     * @param $akamaiHost
     * @param $accessToken
     * @param $clientToken
     */
    private function writeCredentialsFile($clientSecret, $akamaiHost, $accessToken, $clientToken)
    {
        $fileHandle = fopen('./.edgerc', 'wb+');

        fwrite($fileHandle,'[default]' . "\n");
        fwrite($fileHandle, sprintf('client_secret = "%s"' . "\n", $clientSecret));
        fwrite($fileHandle, sprintf('host = "%s"' . "\n", $akamaiHost));
        fwrite($fileHandle, sprintf('access_token = "%s"' . "\n", $accessToken));
        fwrite($fileHandle, sprintf('client_token = "%s"' . "\n", $clientToken));
    }
}
