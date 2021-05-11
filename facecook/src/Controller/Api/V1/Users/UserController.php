<?php

namespace App\Controller\Api\V1\Users;

use App\Entity\User;
use App\Form\UserAvatarType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\ImageUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * @Route("/api/v1/private/users", name="api_v1_private_users_")
 */
class UserController extends AbstractController
{
    /**
     * @Route("", name="browse", methods={"GET"})
     */
    public function browse(UserRepository $userRepository): Response
    {
        $users = $userRepository->findUsersByPublicStatus(2);
        return $this->json($users, 200, [], [
            'groups' => ['browse_users'],
        ]);
    }

     /**
     * @Route("/{id}", name="read", methods={"GET"}, requirements={"id": "\d+"})
     */
    public function read(User $user): Response
    {
        return $this->json($user, 200, [], [
            'groups' => ['read_users'],
        ]);
    }

    /**
     * @Route("", name="add", methods={"POST"})
     */
    public function add(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['csrf_protection' => false]);

        $sentData = json_decode($request->getContent(), true);
        $form->submit($sentData);

        if ($form->isValid()) {

            // Before submitting the new user, the password needs to be hashed. 
            $password = $form->get('password')->getData();
            $user->setPassword($passwordEncoder->encodePassword($user, $password));
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->json($user, 201, []);
            
        }

        return $this->json($form->getErrors(true, false)->__toString(), 400);
    }

    /**
     * @Route("/{id}/avatar", name="edit_avatar", methods={"POST"})
     */
    public function uploadAvatar(User $user, Request $request, ImageUploader $imageUploader, ValidatorInterface $validator): Response
    {
        // We'll check if the user has the right to edit.
        //$this->denyAccessUnlessGranted('edit', $user);
        // retrieving the avatar in the request
        $avatar = $request->files->get('avatar');

        // validation of the file, adding constraints
        $violations = $validator->validate(
            $avatar,
            [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/*']
                ])
            ]
        );

        // If there ara violations, return error 400
        if ($violations->count() > 0) {
            return $this->json($violations, 400);
        }

        // The uploaded file is valid
        // The filename is changed and the file goes in the directory set in .env
        $newFileName = $imageUploader->uploadUserAvatar($avatar);
        $user->setAvatar($newFileName);

        // Persist the recipe in the database
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();


        return $this->json($user, 200, [], [
            'groups' => ['browse_users', 'read_users'],
        ]);
    }

    /**
     * @Route("/{id}", name="edit", methods={"PUT", "PATCH"}, requirements={"id": "\d+"})
     */
    public function edit(User $user, Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        // We'll check if the user has the right to edit.
        $this->denyAccessUnlessGranted('edit', $user);

        $form = $this->createForm(UserType::class, $user, ['csrf_protection' => false]);

        $sentData = json_decode($request->getContent(), true);
        $form->submit($sentData);

        if ($form->isValid()) {
            // Before submitting the new user, the password needs to be hashed. 
            $password = $form->get('password')->getData();
            $user->setPassword($passwordEncoder->encodePassword($user, $password));
            
            $this->getDoctrine()->getManager()->flush();

            return $this->json($user, 200, [], [
                'groups' => ['read_users'],
            ]);
        }
        return $this->json($form->getErrors(true, false)->__toString(), 400);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"}, requirements={"id": "\d+"})
     */
    public function delete (User $user): Response
    {
        //! Deleting a specific user is impossible with that method because of the foreign key in the recipe table 
        //! This was expected behavior. 
        //$this->denyAccessUnlessGranted('delete', $user);

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return $this->json(null, 204);
    }
}
