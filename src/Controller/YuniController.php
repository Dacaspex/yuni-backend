<?php

namespace App\Controller;

use App\Auth\TokenValidator;
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
     * @param string $token
     * @param TokenValidator $validator
     * @return Response
     * @Route("/auth/{token}")
     */
    public function testAuth(string $token, TokenValidator $validator): Response
    {
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

    /**
     * @param CanteenService $service
     * @return Response
     * @Route("/menu_items")
     */
    public function getAllMenuItems(CanteenService $service): Response
    {
        return new JsonResponse($service->getAllMenuItems());
    }
}