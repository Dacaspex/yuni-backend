<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class YuniController
{
    /**
     * @return JsonResponse
     * @Route("/test")
     */
    public function test() {
        return new JsonResponse(['message' => 'Hello World!']);
    }
}