<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FileHandler extends AbstractController
{
    public function __invoke()
    {
        return $this->render('uploader.html.twig');
    }
}