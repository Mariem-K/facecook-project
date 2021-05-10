<?php

namespace App\Controller\Api\V1\Users;

use App\Entity\Category;
use App\Entity\Recipe;
use App\Entity\User;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use App\Service\RecipeSlugger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/private/recipes", name="api_v1_private_recipes_")
 */
class RecipeController extends AbstractController
{
    /**
     * @Route("", name="browse", methods={"GET"})
     */
    public function browse(RecipeRepository $recipeRepository): Response
    {
        // initialization of the criteria of the request
        $criteria = [];
        // Looking if the parameters title or status exist and add them to the criteria
        if (isset($_GET['title'])) {
            $criteria = ['title' => $_GET['title']];
        }
        
        if (isset($_GET['status'])) {
            $criteria = ['status' => $_GET['status']];
        }

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

        // Retrieves the recipes of the user who is connected
        $recipes = $recipeRepository->findBy(['user' => $this->getUser()]);


        return $this->json($recipes, 200, [], [
            'groups' => ['browse_recipes', 'browse_categories'],
        ]);
    }

    /**
     * @Route("/{id}", name="read", methods={"GET"}, requirements={"id": "\d+"})
     */
    public function read(Recipe $recipe): Response
    {
        return $this->json($recipe, 200, [], [
            'groups' => ['read_recipes', 'read_users', 'read_categories'],
        ]);
    }

    /**
     * @Route("", name="add", methods={"POST"})
     */
    public function add(Request $request, RecipeSlugger $slugger): Response
    {
        $recipe = new Recipe();
        
        //dd($this->getUser());

        $form = $this->createForm(RecipeType::class, $recipe, ['csrf_protection' => false]);

        $sentData = json_decode($request->getContent(), true);
        $form->submit($sentData);

        if ($form->isValid()) {
            $recipe->setSlug($slugger->slugify($recipe->getTitle()));
            $recipe->setUser($this->getUser());
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($recipe);
            $em->flush();

            return $this->json($recipe, 201, [], [
                'groups' => ['read_recipes', 'read_users', 'read_categories'],
            ]);
        }

        return $this->json($form->getErrors(true, false)->__toString(), 400);
    }

    /**
     * @Route("/{id}", name="edit", methods={"PUT", "PATCH"}, requirements={"id": "\d+"})
     */
    public function edit(Recipe $recipe, Request $request, RecipeSlugger $slugger): Response
    {
        // We'll check if the user has the right to edit.
        $this->denyAccessUnlessGranted('edit', $recipe);

        $form = $this->createForm(RecipeType::class, $recipe, ['csrf_protection' => false]);

        $sentData = json_decode($request->getContent(), true);
        $form->submit($sentData);

        if ($form->isValid()) {
            $recipe->setSlug($slugger->slugify($recipe->getTitle()));

            // This updates the "updated at" property in the database. 
            $recipe->setUpdatedAt(new \DateTime());

            $this->getDoctrine()->getManager()->flush();

            return $this->json($recipe, 200, [], [
                'groups' => ['read_recipes', 'read_users', 'read_categories'],
            ]);
        }

        return $this->json($form->getErrors(true, false)->__toString(), 400);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"}, requirements={"id": "\d+"})
     */
    public function delete (Recipe $recipe): Response
    {
        $this->denyAccessUnlessGranted('delete', $recipe);

        $em = $this->getDoctrine()->getManager();
        $em->remove($recipe);
        $em->flush();

        return $this->json(null, 204);
    }
}