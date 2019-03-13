<?php

namespace App\Controller;

use App\Service\CanteenService;
use App\Storage\Exception\NotFoundException;
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
     * @param int $id
     * @param CanteenService $service
     * @return Response
     * @Route("/api/canteens/{id}")
     */
    public function getCanteen(int $id, CanteenService $service): Response
    {
        try {
            return new JsonResponse($service->getCanteen($id));
        } catch (NotFoundException $e) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }
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