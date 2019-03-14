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
        try {
            $json  = Json::parse($request->getContent());
            $token = $json->field('token')->string();
        } catch (AssertionFailed $e) {
            return new JsonResponse(['message' => 'Could not find token field'], Response::HTTP_BAD_REQUEST);
        } catch (CantDecode $e) {
            return new JsonResponse(['message' => 'Could not decode json'], Response::HTTP_BAD_REQUEST);
        }

        if ($tokenValidator->check($token)) {
            return new JsonResponse(['message' => 'Token valid'], Response::HTTP_OK);
        } else {
            return new JsonResponse(['message' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @param Request $request
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
        try {
            $json = Json::parse($request->getContent());

            $token = $json->field('token')->string();
            if (!$tokenValidator->check($token)) {
                return $this->handleUnAuthorised();
            }

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
        try {
            $json = Json::parse($request->getContent());

            $token = $json->field('token')->string();
            if (!$tokenValidator->check($token)) {
                return $this->handleUnAuthorised();
            }

            $service->removeItemFromMenu($menuId);

            return new JsonResponse();
        } catch (CantDecode | AssertionFailed $e) {
            return $this->handleParseException($e);
        } catch (NotFoundException $e) {
            return $this->handleNotFound($e);
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
     * @param \Exception $e
     * @return Response
     */
    private function handleNotFound(\Exception $e): Response
    {
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}