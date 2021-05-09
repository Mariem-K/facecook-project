<?php

namespace App\Controller\Api\V1\Visitor;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/public/recipes", name="api_v1_public_recipes_")
 */
class RecipeController extends AbstractController
{
   /**
     * @Route("", name="browse", methods={"GET"})
     */
    public function browse(RecipeRepository $recipeRepository): Response
    {
        // initialization of the criteria of the request, we only want to retrieve the public recipes, 
        // ie the recipes with the status 2
        $criteria = ['status' => '2'];
        
        // initialization of the variable $orderBy
        $orderBy = [];

        // if the parameter sort exist, add it to the orderBy variable. 
        // The first sign indicates if the sort is ASC (+) or DESC (-)
        if (isset($_GET['sort'])) {
            $sort = $_GET['sort'];
            $order = substr($sort,0,1);
            $order = $order === '-' ? 'DESC' : 'ASC';
            $orderParameter = substr($sort, 1);
            $orderBy = [$orderParameter => $order];
        }
        
        // determination of the limit if the parameter exist
        $limit = (isset($_GET['limit'])) ? $_GET['limit'] : null;

        // Retrieve all the recipes with the criteria, sort and limit
        $recipes = $recipeRepository->findBy($criteria, $orderBy, $limit);

        return $this->json($recipes, 200, [], [
            'groups' => ['browse_recipes', 'browse_categories'],
        ]);
    }

    /**
     * @Route("/{id}", name="read", methods={"GET"}, requirements={"id": "\d+"})
     */
    public function read(Recipe $recipe): Response
    {
        // we check if the recipe is public, else it's forbidden
        if ($recipe->getStatus() == 2) {
            return $this->json($recipe, 200, [], [
                'groups' => ['read_recipes', 'read_users', 'read_categories'],
                ]);
        } else {
            $error = 'Denied access';
            return $this->json($error, 403);
        }

    }
}
