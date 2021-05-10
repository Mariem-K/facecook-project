<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class RecipeVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['edit', 'delete', 'add'])
            && $subject instanceof \App\Entity\Recipe;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'edit':
            case 'delete':
                // logic to determine if the user can VIEW
                // return true or false
                if ($subject->getUser() === $user) {
                    return true;
                }
                break;
            case 'add':
                // logic to determine if the user exists and can add a recipe
                if (isset($user)) {
                    return true;
                }
                break;
        }

        return false;
    }
}
