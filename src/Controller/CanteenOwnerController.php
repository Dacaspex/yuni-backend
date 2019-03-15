<?php

namespace App\Controller;

use App\Auth\TokenValidator;
use App\Models\Schedule;
use App\Service\CanteenOwnerService;
use App\Storage\Exception\NotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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