<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeImageUploadType;
use App\Service\ImageUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    /**
     * @Route("/test/{id}", name="test")
     */
    public function add(Recipe $recipe, ImageUploader $imageUploader, Request $request)
    {  
        
        $form = $this->createForm(RecipeImageUploadType::class, $recipe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On traite le fichier reçu
            // On le récupère dans une variable
            $image = $form->get('imageFile')->getData();
            
            $newFileName = $imageUploader->uploadRecipePictures($image);
            $recipe->setImage($newFileName);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($recipe);
            $entityManager->flush();

            $this->addFlash('success', 'Image ajoutée');

            return $this->redirectToRoute('test', ['id' => $recipe->getId()]);
        }

        return $this->render('test/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
