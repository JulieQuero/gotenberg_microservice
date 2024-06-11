<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GotenbergCall;

class ConvertPdfController extends AbstractController
{
    public function __construct(
        private readonly GotenbergCall $gotenbergCall,
        private LoggerInterface $logger
    ) {
    }

    #[Route('/convert/pdf', name: 'app_convert_pdf', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        $url = $request->request->get('url');

        if (!$url) {
            return $this->json(['error' => 'URL parameter is missing.'], 400);
        }

        $response = $this->gotenbergCall->convertToPdf($url);

        if ($response['success']) {
            return $this->json(['pdf_content' => $response['content']]);
        }

        return $this->json(['error' => $response['error']], 500);
    }
}
