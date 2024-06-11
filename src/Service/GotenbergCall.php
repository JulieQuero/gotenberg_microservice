<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GotenbergCall
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private LoggerInterface $logger
    ) {
    }

    public function convertToPdf(string $url): array
    {
        try {
            $response = $this->client->request('POST', $_ENV['GOTENBERG_URL'], [
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                ],
                'body' => [
                    'url' => $url,
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                return ['success' => true, 'content' => $response->getContent()];
            } else {
                return ['success' => false, 'error' => 'La requête à l\'API Gotenberg a échoué avec le statut ' . $statusCode];
            }
        } catch (\Exception $e) {
            $this->logger->error('Une erreur s\'est produite lors de la génération du PDF : ' . $e->getMessage());
            return ['success' => false, 'error' => 'Une erreur s\'est produite lors de la génération du PDF : ' . $e->getMessage()];
        }
    }
}
