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

    public function convertToPdf(string $url): string
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
                $pdfContent = $response->getContent();
                $fileName = uniqid('document_', true) . '.pdf';
                $pdfFilePath = 'pdfs/' . $fileName;

                $filesystem = new Filesystem();
                $filesystem->dumpFile($pdfFilePath, $pdfContent);

                if ($filesystem->exists($pdfFilePath)) {
                    return 'Le fichier PDF a été créé avec succès à l\'emplacement : ' . $pdfFilePath;
                } else {
                    return 'Impossible de créer le fichier PDF.';
                }
            } else {
                return 'La requête à l\'API Gotenberg a échoué avec le statut ' . $statusCode;
            }
        } catch (\Exception $e) {
            $this->logger->error('Une erreur s\'est produite lors de la génération du PDF : ' . $e->getMessage());
            return 'Une erreur s\'est produite lors de la génération du PDF : ' . $e->getMessage();
        }
    }
}
