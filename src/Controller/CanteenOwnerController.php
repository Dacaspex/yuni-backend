<?php

namespace App\Controller;

use App\Auth\TokenValidator;
use App\Models\Schedule;
use App\Service\CanteenOwnerService;
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
     * @param string $token
     * @param TokenValidator $tokenValidator
     * @param CanteenOwnerService $service
     * @return Response
     * @Route("/add_menu_item_to_menu/{token}/")
     */
    public function addMenuItemToMenu(
        Request $request,
        string $token,
        TokenValidator $tokenValidator,
        CanteenOwnerService $service
    ): Response {
        if (!$tokenValidator->check($token)) {
            return $this->handleUnAuthorised();
        }

        $content = $request->getContent();

        try {
            $json = Json::parse($content);

            $canteenId  = $json->field('canteen_id')->int();
            $menuItemId = $json->field('menu_item_id')->int();
            $schedule   = Schedule::fromString($json->field('schedule')->string());

            $service->addMenuItemToMenu($canteenId, $menuItemId, $schedule);
        } catch (CantDecode | AssertionFailed $e) {
            return $this->handleParseException($e);
        }
    }

    private function handleUnAuthorised(): Response
    {
        return new JsonResponse(
            [
                'message' => 'The supplied token is not valid'
            ],
            403
        )
    }

    private function handleParseException(CantDecode $e): Response
    {
        return new JsonResponse(
            [
                'message' => 'Could not parse json'
            ],
            500
        );
    }
}