<?php

namespace App\Controller;

use App\Service\Solver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /** @var Solver $solver */
    protected $solver;

    public function __construct(Solver $solver)
    {
        $this->solver = $solver;
    }

    #[Route('/', name: 'home')]
    public function __invoke()
    {
        return new Response($this->solver->process());
    }
}
