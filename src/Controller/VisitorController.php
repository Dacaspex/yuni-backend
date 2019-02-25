<?php

namespace App\Controller;

use App\Service\CanteenService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VisitorController
{
    /**
     * @param CanteenService $service
     * @return Response
     * @Route("/api/canteens")
     */
    public function getCanteens(CanteenService $service): Response
    {
        $canteens = $service->getCanteens();

        return new JsonResponse($canteens);
    }

    /**
     * @param CanteenService $service
     * @return Response
     * @Route("/api/all_menu_items")
     */
    public function getAllMenuItems(CanteenService $service): Response
    {
        return new JsonResponse($service->getAllMenuItems());
    }
}