<?php

namespace App\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\UrlHelper;

class UpdateTokenResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ?array $user = [],
        private ?UrlHelper $urlHelper = null
    ) {
    }
    public function onUserResolve($event): void
    {

        //TODO Evaluate if auth is true
        $user = $event->getUser();
        if ($user) {

            $this->user = [
                'fullName' => $user->getFirstName() . ' ' . $user->getLastname(),
                'email' => $user->getEmail(),
                'avatar' => $user->getAvatar() ? $this->urlHelper->getAbsoluteUrl('/storage/default/' . $user->getAvatar()) : null,
                'roleGroups' => array_map(function ($group) {
                    return $group->getName();
                }, $user->getRoleGroups()->toArray())
            ];
        }
    }

    public function onLeagueOauth2ServerEventTokenRequestResolve($event): void
    {
        if ($event->getResponse()->getStatusCode() === 200) {

            $response = $event->getResponse();
            $content = json_decode($event->getResponse()->getContent(), true);
            $content['user'] = $this->user;

            $newContent = json_encode($content, true);
            $response->setContent($newContent);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'league.oauth2_server.event.user_resolve' => 'onUserResolve',
            'league.oauth2_server.event.token_request_resolve' => 'onLeagueOauth2ServerEventTokenRequestResolve',
        ];
    }
}
