<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GotenbergCall;


class ConvertPdfController extends AbstractController
{
    public function __construct(
        private readonly GotenbergCall $gotenbergCall,
    ) {
    }

    #[Route('/convert/pdf/{url}', name: 'app_convert_pdf', methods: ['POST'])]
    public function index(string $url): JsonResponse
    {
        if (!$url) {
            return $this->json(['error' => 'URL parameter is missing.'], 400);
        }

        $response = $this->gotenbergCall->convertToPdf($url);

        if ($response['success']) {
            $pdfContent = base64_encode($response['content']);
            return $this->json(['pdf_content' => $pdfContent]);
        }

        return $this->json(['error' => $response['error']], 500);
    }
}
