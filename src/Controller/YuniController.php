<?php

namespace App\Controller;

use App\Service\CanteenService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class YuniController
{
    /**
     * @return JsonResponse
     * @Route("/test")
     */
    public function test(): Response
    {
        return new JsonResponse(['message' => 'Hello World!']);
    }

    /**
     * @param CanteenService $service
     * @return Response
     * @Route("/canteens")
     */
    public function getCanteens(CanteenService $service): Response
    {
        $canteens = $service->getCanteens();

        return new JsonResponse($canteens);
    }
}