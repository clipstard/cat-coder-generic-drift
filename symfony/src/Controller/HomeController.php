<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class HomeController
{
    public function __invoke()
    {
        return new JsonResponse(['asd']);
    }
}