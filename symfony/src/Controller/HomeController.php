<?php

namespace App\Controller;

use App\Service\Solver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    /** @var Solver $solver */
    protected $solver;

    public function __construct(Solver $solver)
    {
        $this->solver = $solver;
    }

    public function __invoke()
    {

//        for ($i = 0; $i < 4; $i++) {
//            $this->solver->solve(1, $i, true);
//        }

        return new Response($this->solver->solve());
//        return new JsonResponse(['response' => $this->solver->solve()]);
    }
}