<?php

namespace App\Controller;

use App\Auth\TokenValidator;
use App\Models\Availability;
use App\Models\Category;
use App\Models\OperatingTimes;
use App\Models\Schedule;
use App\Service\CanteenOwnerService;
use App\Storage\Exception\NotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vcn\Lib\Enum\Exception\InvalidInstance;
use Vcn\Pipette\Json;
use Vcn\Pipette\Json\Exception\AssertionFailed;
use Vcn\Pipette\Json\Exception\CantDecode;

class CanteenOwnerController
{
    /**
     * @param Request $request
     * @param TokenValidator $tokenValidator
     * @return Response
     * @Route("/api/authenticate")
     */
    public function authenticate(Request $request, TokenValidator $tokenValidator): Response
    {
        if ($tokenValidator->check($request)) {
            return new JsonResponse(['authenticated' => true]);
        } else {
            return new JsonResponse(['authenticated' => false]);
        }
    }

    /**
     * @param Request $request
     * @param TokenValidator $tokenValidator
     * @param CanteenOwnerService $service
     * @return Response
     * @Route("/api/menu_items", methods={"POST"})
     */
    public function addNewMenuItem(
        Request $request,
        TokenValidator $tokenValidator,
        CanteenOwnerService $service
    ): Response {
        if (!$tokenValidator->check($request)) {
            return $this->handleUnAuthorised();
        }

        try {
            $json = Json::parse($request->getContent());

            $name        = $json->field('name')->string();
            $description = $json->field('description')->string();
            $category    = Category::byName($json->field('category')->string());

            $service->addNewMenuItem($name, $description, $category);

            return new JsonResponse();
        } catch (CantDecode | AssertionFailed | InvalidInstance $e) {
            return $this->handleParseException($e);
        }
    }

    /**
     * @param int $id
     * @param Request $request
     * @param TokenValidator $tokenValidator
     * @param CanteenOwnerService $service
     * @return Response
     * @Route("/api/menu_items/{id}", methods={"PATCH"})
     */
    public function updateMenuItem(
        int $id,
        Request $request,
        TokenValidator $tokenValidator,
        CanteenOwnerService $service
    ): Response {
        if (!$tokenValidator->check($request)) {
            return $this->handleUnAuthorised();
        }

        try {
            $json = Json::parse($request->getContent());

            $name        = $json->field('name')->string();
            $description = $json->field('description')->string();
            $category    = Category::byName($json->field('category')->string());

            $service->updateMenuItem($id, $name, $description, $category);

            return new JsonResponse();
        } catch (CantDecode | AssertionFailed | InvalidInstance $e) {
            return $this->handleParseException($e);
        }
    }

    /**
     * @param Request $request 
     * @param TokenValidator $tokenValidator
     * @param CanteenOwnerService $service
     * @return Response
     * @Route("/api/menu", methods={"POST"})
     */
    public function addItemToMenu(
        Request $request,
        TokenValidator $tokenValidator,
        CanteenOwnerService $service
    ): Response {
        if (!$tokenValidator->check($request)) {
            return $this->handleUnAuthorised();
        }

        try {
            $json = Json::parse($request->getContent());

            $canteenId  = $json->field('canteen_id')->int();
            $menuItemId = $json->field('menu_item_id')->int();
            $schedule   = Schedule::fromBitMask($json->field('schedule')->string());

            $service->addMenuItemToMenu($canteenId, $menuItemId, $schedule);

            return new JsonResponse();
        } catch (CantDecode | AssertionFailed $e) {
            return $this->handleParseException($e);
        }
    }

    /**
     * @param int $menuId
     * @param Request $request
     * @param TokenValidator $tokenValidator
     * @param CanteenOwnerService $service
     * @return Response
     * @Route("/api/menu/{menuId}", methods={"DELETE"})
     */
    public function removeItemFromMenu(
        int $menuId,
        Request $request,
        TokenValidator $tokenValidator,
        CanteenOwnerService $service
    ): Response {
        if (!$tokenValidator->check($request)) {
            return $this->handleUnAuthorised();
        }

        try {
            $service->removeItemFromMenu($menuId);

            return new JsonResponse();
        } catch (NotFoundException $e) {
            return $this->handleNotFound();
        }
    }

    /**
     * @param int $id
     * @param Request $request
     * @param TokenValidator $tokenValidator
     * @param CanteenOwnerService $service
     * @return Response
     * @Route("/api/canteens/{id}", methods={"PATCH"})
     */
    public function updateCanteen(
        int $id,
        Request $request,
        TokenValidator $tokenValidator,
        CanteenOwnerService $service
    ): Response {
        if (!$tokenValidator->check($request)) {
            return $this->handleUnAuthorised();
        }

        try {
            $json = Json::parse($request->getContent());

            $name           = $json->field('name')->string();
            $description    = $json->field('description')->string();
            $operatingTimes = OperatingTimes::fromJson($json->field('operating_times'));

            $service->updateCanteen($id, $name, $description, $operatingTimes);

            return new JsonResponse();
        } catch (CantDecode | AssertionFailed $e) {
            return $this->handleParseException($e);
        }
    }

    /**
     * @param int $id
     * @param Request $request
     * @param TokenValidator $tokenValidator
     * @param CanteenOwnerService $service
     * @return Response
     * @Route("/api/menu/{id}/schedule", methods={"PATCH"})
     */
    public function updateMenuItemSchedule(
        int $id,
        Request $request,
        TokenValidator $tokenValidator,
        CanteenOwnerService $service
    ): Response {
        if (!$tokenValidator->check($request)) {
            return $this->handleUnAuthorised();
        }

        try {
            $json     = Json::parse($request->getContent());
            $schedule = $json->field('schedule')->string();

            $service->updateMenuItemSchedule($id, $schedule);

            return new JsonResponse();
        } catch (CantDecode | AssertionFailed $e) {
            return $this->handleParseException($e);
        }
    }

    /**
     * @param int $id
     * @param Request $request
     * @param TokenValidator $tokenValidator
     * @param CanteenOwnerService $service
     * @return JsonResponse|Response
     * @Route("/api/menu/{id}/availability", methods={"PATCH"})
     */
    public function updateMenuItemAvailability(
        int $id,
        Request $request,
        TokenValidator $tokenValidator,
        CanteenOwnerService $service
    ) {
        if (!$tokenValidator->check($request)) {
            return $this->handleUnAuthorised();
        }

        try {
            $json     = Json::parse($request->getContent());
            $schedule = Availability::byName($json->field('availability')->string());

            $service->updateMenuItemAvailability($id, $schedule);

            return new JsonResponse();
        } catch (CantDecode | AssertionFailed | InvalidInstance $e) {
            return $this->handleParseException($e);
        }
    }

    /**
     * @param int $id
     * @param Request $request
     * @param TokenValidator $tokenValidator
     * @param CanteenOwnerService $service
     * @return Response
     * @Route("/api/menu_items/{id}", methods={"DELETE"})
     */
    public function removeMenuItem(
        int $id,
        Request $request,
        TokenValidator $tokenValidator,
        CanteenOwnerService $service
    ): Response {
        if (!$tokenValidator->check($request)) {
            return $this->handleUnAuthorised();
        }

        $service->removeMenuItem($id);

        return new JsonResponse();
    }

    /**
     * @return Response
     */
    private function handleUnAuthorised(): Response
    {
        return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param \Exception $e
     * @return Response
     */
    private function handleParseException(\Exception $e): Response
    {
        return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @return Response
     */
    private function handleNotFound(): Response
    {
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}