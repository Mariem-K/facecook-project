<?php

namespace App\Controller\Api\V1\Users;

use App\Entity\Category;
use App\Entity\Recipe;
use App\Entity\User;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use App\Repository\UserRepository;
use App\Service\ImageOptimizer;
use App\Service\ImageUploader;
use App\Service\RecipeSlugger;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

         // Retrieves the recipes of the user who is connected
         $recipes = $recipeRepository->findBy(['user' => $this->getUser()], $orderBy, $limit);
         $user = $this->getUser();

         // Retrieves the visibles recipes
         $visibleRecipes = $user->getVisibleRecipes();

         // Merge the two collections of recipes
         $recipesList = new ArrayCollection(
             array_merge($recipes, $visibleRecipes->toArray())
         );
                 
         return $this->json($recipesList, 200, [], [
             'groups' => ['browse_recipes', 'browse_categories'],
        ]);
    }

    /**
     * @Route("/{id}", name="read", methods={"GET"}, requirements={"id": "\d+"})
     */
    public function read(Recipe $recipe): Response
    {
        // we check if the user has the right to access this recipe
        if ($recipe->getUser() == $this->getUser()) {
            
            return $this->json($recipe, 200, [], [
                'groups' => ['read_recipes', 'read_users', 'read_categories'],
            ]);
        } else {
            $error = 'Denied access';
            return $this->json($error, 403);
        }
    }

    /**
     * @Route("", name="add", methods={"POST"})
     */
    public function add(Request $request, RecipeSlugger $slugger, UserRepository $userRepository): Response
    {
        $recipe = new Recipe();

        // We'll check if the user has the right to add
        $this->denyAccessUnlessGranted('add', $recipe);
        
        $form = $this->createForm(RecipeType::class, $recipe, ['csrf_protection' => false]);

        $sentData = json_decode($request->getContent(), true);
        $form->submit($sentData);

        if ($form->isValid()) {
            $recipe->setSlug($slugger->slugify($recipe->getTitle()));
            // The user connected is associated with the recipe
            $recipe->setUser($this->getUser());
            // An image by default is associated to a recipe
            $recipe->setImage('default_img_recipe.png');
            
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
     * @Route("/{id}/image", name="edit_image", methods={"POST"}, requirements={"id": "\d+"})
     */
    public function uploadImage(Recipe $recipe, Request $request, ImageUploader $imageUploader, ValidatorInterface $validator): Response
    {
        // We'll check if the user has the right to edit.
        $this->denyAccessUnlessGranted('edit', $recipe);

        // retrieving the image in the request
        $image = $request->files->get('imageFile');

        // validation of the file, adding constraints
        $violations = $validator->validate(
            $image,
            [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/*']
                ])
            ]
        );

        // If there are violations, return error 400
        if ($violations->count() > 0) {
            return $this->json($violations, 400);
        }

        // The uploaded file is valid
        // The filename is changed and the file goes in the directory set in .env
        $newFileName = $imageUploader->uploadRecipePictures($image);
        $recipe->setImage($newFileName);
        $recipe->setUpdatedAt(new \DateTime());

        // Persist the recipe in the database
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($recipe);
        $entityManager->flush();

        return $this->json($recipe, 200, [], [
            'groups' => ['read_recipes', 'read_users', 'read_categories'],
        ]);
       
    }

    /**
     * @Route("/{id}", name="edit", methods={"PUT", "PATCH"}, requirements={"id": "\d+"})
     */
    public function edit(Recipe $recipe, Request $request, RecipeSlugger $slugger, UserRepository $userRepository): Response
    {
        // We'll check if the user has the right to edit.
        $this->denyAccessUnlessGranted('edit', $recipe);

        $form = $this->createForm(RecipeType::class, $recipe, ['csrf_protection' => false]);

        $sentData = json_decode($request->getContent(), true);
        $form->submit($sentData);

        if ($form->isValid()) {
            $recipe->setSlug($slugger->slugify($recipe->getTitle()));

            // The user connected is associated with the recipe
            $recipe->setUser($this->getUser());

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