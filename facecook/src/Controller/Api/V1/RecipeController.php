<?php

namespace App\Controller\Api\V1;

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
 * @Route("/api/v1/recipes", name="api_v1_recipes_")
 */
class RecipeController extends AbstractController
{
    /**
     * @Route("", name="browse", methods={"GET"})
     */
    public function browse(RecipeRepository $recipeRepository): Response
    {
        // if there is a parameter sort which value is -created_at in the requested url, retrieve the last 5 created public recipes
        // else retrieve all recipes
        if (isset($_GET['sort']) && isset($_GET['status']) && $_GET['sort'] == '-created_at') {
            $recipes = $recipeRepository->findBy(['status' => $_GET['status']], ['created_at' => 'DESC'], 5);
        } else {
            $recipes = $recipeRepository->findAll();
        }

        return $this->json($recipes, 200, [], [
            'groups' => ['browse'],
        ]);
    }

    /**
     * @Route("/{id}", name="read", methods={"GET"}, requirements={"id": "\d+"})
     */
    public function read(Recipe $recipe): Response
    {
        return $this->json($recipe, 200, [], [
            'groups' => ['read'],
        ]);
    }

    /**
     * @Route("", name="add", methods={"POST"})
     */
    public function add(Request $request, RecipeSlugger $slugger): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe, ['csrf_protection' => false]);

        $sentData = json_decode($request->getContent(), true);
        $form->submit($sentData);

        if ($form->isValid()) {
            $recipe->setSlug($slugger->slugify($recipe->getTitle()));

            //! This line causes errors on Insomnia. The recipe needs to be associated to a user.
            //$recipe->setUser($this->getUser());
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($recipe);
            $em->flush();

            return $this->json($recipe, 201, [], [
                'groups' => ['read'],
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
        //$this->denyAccessUnlessGranted('edit', $recipe);

        $form = $this->createForm(RecipeType::class, $recipe, ['csrf_protection' => false]);

        $sentData = json_decode($request->getContent(), true);
        $form->submit($sentData);

        if ($form->isValid()) {
            $recipe->setSlug($slugger->slugify($recipe->getTitle()));

            // This updates the "updated at" property in the database. 
            $recipe->setUpdatedAt(new \DateTime());

            
            // The recipe needs to be associated to a user.
            //$recipe->setUser($this->getUser());

            $this->getDoctrine()->getManager()->flush();

            return $this->json($recipe, 200, [], [
                'groups' => ['read'],
            ]);
        }

        return $this->json($form->getErrors(true, false)->__toString(), 400);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"}, requirements={"id": "\d+"})
     */
    public function delete (Recipe $recipe): Response
    {
        //$this->denyAccessUnlessGranted('delete', $recipe);

        $em = $this->getDoctrine()->getManager();
        $em->remove($recipe);
        $em->flush();

        return $this->json(null, 204);
    }
}