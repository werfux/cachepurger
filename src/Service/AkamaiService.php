<?php
declare(strict_types=1);

namespace BC\Purger\Service;

use Akamai\Open\EdgeGrid\Client;
use BC\Purger\Helper\AkamaiHelper;
use Symfony\Component\Console\Exception\RuntimeException;

/**
 * Class AkamaiService
 * @package BC\Purger\Service
 */
class AkamaiService
{
    private $client;

    /**
     * AkamaiService constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $akamaiClient = $this->createClient();

        if (null === $akamaiClient) {
            throw new RuntimeException('Unable to initialize Akamai Client.');
        }

        $this->client = $akamaiClient;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function createClient()
    {
        if (AkamaiHelper::validEdgeRcFileExists()) {
            return Client::createFromEdgeRcFile('default', AkamaiHelper::getEdgeRcFilePath(), [
                'verify' => false
            ]);
        }

        return null;
    }

    /**
     * @param $hostname
     * @param array $routes
     * @return mixed
     * @throws \Exception
     */
    public function postPurgeRequest($hostname, array $routes)
    {
        $purgeBody = [
          'hostname' => $hostname,
          'objects' => $routes,
        ];

        /** @var \GuzzleHttp\Psr7\Response $postPurge */
        $postPurge = $this->client->post('/ccu/v3/invalidate/url', [
          'body' => json_encode($purgeBody),
          'headers' => ['Content-Type' => 'application/json']
        ]);

        if ($postPurge->getStatusCode() !== 201) {
            throw new RuntimeException('Purge request was not succesful.');
        }

        $responseBody = json_decode($postPurge->getBody()->getContents());

        return $responseBody;
    }

}