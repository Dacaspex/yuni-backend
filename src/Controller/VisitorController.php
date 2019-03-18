<?php

namespace App\Controller;

use App\Service\Exception\ReviewTooLongException;
use App\Service\VisitorService;
use App\Storage\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vcn\Pipette\Json;
use Vcn\Pipette\Json\Exception\AssertionFailed;
use Vcn\Pipette\Json\Exception\CantDecode;

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
     * @Route("/api/menu_items/{id}/reviews", methods={"GET"})
     */
    public function getMenuItemReviews(int $id, VisitorService $service): Response
    {
        // Feature: Limit and offset
        return new JsonResponse($service->getMenuItemReviews($id));
    }

    /**
     * @param int $id
     * @param VisitorService $service
     * @return Response
     * @Route("/api/canteens/{id}/reviews", methods={"GET"})
     */
    public function getCanteenReviews(int $id, VisitorService $service): Response
    {
        return new JsonResponse($service->getCanteenReviews($id));
    }

    /**
     * @param int $id
     * @param Request $request
     * @param VisitorService $service
     * @return Response
     * @Route("/api/menu_items/{id}/reviews", methods={"POST"})
     */
    public function addMenuItemReview(int $id, Request $request, VisitorService $service): Response
    {
        try {
            $json = Json::parse($request->getContent());

            $rating      = $json->field('rating')->int();
            $description = $json->field('description')->string();

            $service->createMenuItemReview($id, $rating, $description);

            return new JsonResponse();
        } catch (CantDecode | AssertionFailed $e) {
            return $this->handleParseException($e);
        } catch (ReviewTooLongException $e) {
            return new JsonResponse(['message' => 'Review too long'], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param int $id
     * @param Request $request
     * @param VisitorService $service
     * @return Response
     * @Route("/api/canteens/{id}/reviews", methods={"POST"})
     */
    public function addCanteenReview(int $id, Request $request, VisitorService $service): Response
    {
        try {
            $json = Json::parse($request->getContent());

            $rating      = $json->field('rating')->int();
            $description = $json->field('description')->string();

            $service->createCanteenReview($id, $rating, $description);

            return new JsonResponse();
        } catch (CantDecode | AssertionFailed $e) {
            return $this->handleParseException($e);
        } catch (ReviewTooLongException $e) {
            return new JsonResponse(['message' => 'Review too long'], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param \Exception $e
     * @return Response
     */
    private function handleParseException(\Exception $e): Response
    {
        return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}