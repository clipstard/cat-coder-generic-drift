<?php

namespace App\Controller;

use App\Service\Solver;
use Symfony\Component\HttpFoundation\JsonResponse;

class HomeController
{
    /** @var Solver $solver */
    protected $solver;

    public function __construct(Solver $solver)
    {
        $this->solver = $solver;
    }

    /**
     * @return JsonResponse
     * @throws \Exception
     */
    public function __invoke()
    {
        return new JsonResponse(['response' => $this->solver->solve()]);
    }
}