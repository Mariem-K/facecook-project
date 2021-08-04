<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    public function onLexikJwtAuthenticationOnAuthenticationSuccess($event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        $data['user'] = array(
            'pseudonym' => $user->getPseudonym(),
            'id' => $user->getId(),
            'status' => $user->getStatus(),
            'avatar' => $user->getAvatar(),
            'friends' => $user->getFriends(),
        );

        $event->setData($data);
    }

    public static function getSubscribedEvents()
    {
        return [
            'lexik_jwt_authentication.on_authentication_success' => 'onLexikJwtAuthenticationOnAuthenticationSuccess',
        ];
    }
}
