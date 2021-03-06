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

        $x = 1;

        return new Response('');
        return new Response($this->solver->solveLast());
//        return new JsonResponse(['response' => $this->solver->solve()]);
    }
}
