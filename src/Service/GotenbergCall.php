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
            $this->logger->info('Gotenberg response status code: ' . $statusCode);

            if ($statusCode === 200) {
                $pdfContent = $response->getContent();
                $fileName = uniqid('document_', true) . '.pdf';
                $pdfFilePath = 'pdfs/' . $fileName;

                $filesystem = new Filesystem();
                $filesystem->dumpFile($pdfFilePath, $pdfContent);

                if ($filesystem->exists($pdfFilePath)) {
                    return ['success' => true, 'content' => $pdfFilePath];
                }
                return ['success' => false, 'error' => 'Le fichier PDF n\'a pas été créé.'];
            }

            return ['success' => false, 'error' => 'La requête à l\'API Gotenberg a échoué avec le statut ' . $statusCode];
        } catch (\Exception $e) {
            $this->logger->error('Une erreur s\'est produite lors de la génération du PDF : ' . $e->getMessage());
            return ['success' => false, 'error' => 'Une erreur s\'est produite lors de la génération du PDF : ' . $e->getMessage()];
        }
    }
}
