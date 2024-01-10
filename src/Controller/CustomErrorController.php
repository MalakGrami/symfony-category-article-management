<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomErrorController extends AbstractController
{
    #[Route('/403', name: 'custom_403')]
    public function error403(): Response
    {
        return $this->render('error/403.html.twig');
    }
}
