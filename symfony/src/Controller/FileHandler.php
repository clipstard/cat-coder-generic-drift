<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FileHandler extends AbstractController
{
    #[Route('/upload', name: 'fileUpload')]
    public function __invoke()
    {
        return $this->render('uploader.html.twig');
    }
}
