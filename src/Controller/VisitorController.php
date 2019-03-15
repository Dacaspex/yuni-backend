<?php

namespace App\Controller;

use App\Service\VisitorService;
use App\Storage\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VisitorController
{
    /**
     * @param VisitorService $service
     * @return Response
     * @Route("/api/canteens")
     */
    public function getCanteens(VisitorService $service): Response
    {
        $canteens = $service->getCanteens();

        return new JsonResponse($canteens);
    }

    /**
     * @param int $id
     * @param VisitorService $service
     * @return Response
     * @Route("/api/canteens/{id}")
     */
    public function getCanteen(int $id, VisitorService $service): Response
    {
        try {
            return new JsonResponse($service->getCanteen($id));
        } catch (NotFoundException $e) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @param VisitorService $service
     * @return Response
     * @Route("/api/menu/all")
     */
    public function getAllMenuItems(VisitorService $service): Response
    {
        return new JsonResponse($service->getAllMenuItems());
    }

    /**
     * @param int $id
     * @param VisitorService $service
     * @return Response
     * @Route("/api/menu_item/{id}/review")
     */
    public function getMenuItemReviews(int $id, VisitorService $service): Response
    {
        // Feature: Limit and offset
        return new JsonResponse($service->getMenuItemReviews($id));
    }

    /**
     * @param int $canteenId
     * @return Response
     * @Route("/api/canteen/{id}/review")
     */
    public function getCanteenReviews(int $canteenId): Response
    {

    }

    public function addMenuItemReview(int $menuItemReview, Request $request): Response
    {

    }

    public function addCanteenReview(int $canteenId): Response
    {

    }
}