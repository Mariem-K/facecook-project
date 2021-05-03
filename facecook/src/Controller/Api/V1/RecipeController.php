<?php

namespace App\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController
{
    /**
     * @Route("/api/v1/recipe", name="api_v1_recipe")
     */
    public function index(): Response
    {
        return $this->render('api/v1/recipe/index.html.twig', [
            'controller_name' => 'RecipeController',
        ]);
    }
}
