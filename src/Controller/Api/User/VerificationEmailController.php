<?php

namespace App\Controller\Api\User;

use App\Repository\Main\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use App\Service\User\GetUser;
use FOS\RestBundle\View\View;

#[Route(path: "/api/verify")]
class VerificationEmailController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    // #[Route('/verify/email', name: 'app_verify_email')]
    #[Rest\Get('/email', name: 'api_user_verify_email')]
    public function verifyUserEmail(
        Request $request,
        TranslatorInterface $translator,
        UserRepository $userRepository,
        GetUser $getUser
    ) {
        try {
            $uuid = $request->get('uuid');
            if (null === $uuid) {
                return View::create('Url bad request format', Response::HTTP_BAD_REQUEST);
            }

            $user = ($getUser)($uuid);
            $this->emailVerifier->handleEmailConfirmation($request, $user);
            // @TODO Change the redirect on success and handle or remove the flash message in your templates
            //TODO: Change redirect Url to correct page
            return $this->redirectToRoute('app_login');
        } catch (VerifyEmailExceptionInterface $exception) {
            return View::create($translator->trans($exception->getReason(), [], 'VerifyEmailBundle'), Response::HTTP_BAD_REQUEST);
        }
    }
}
