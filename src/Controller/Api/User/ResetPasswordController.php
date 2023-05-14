<?php

namespace App\Controller\Api\User;

use App\Entity\Main\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use App\Service\User\GetUser;
use App\Service\User\ResetPassword\UserResetPasswordFormProcessor;
use App\Service\User\ResetPassword\UserResetPasswordRequestFormProcessor;
use Error;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Display & process form to request a password reset.
     */
    // #[Route('', name: 'app_forgot_password_request')]
    #[Rest\Post('', name: 'app_forgot_password_request')]
    public function request(
        Request $request,
        MailerInterface $mailer,
        TranslatorInterface $translator,
        UserResetPasswordRequestFormProcessor $userResetPasswordRequestFormProcessor,
        SerializerInterface $serializer,
        EmailVerifier $emailVerifier
    ) {
        try {
            [$user, $error] = ($userResetPasswordRequestFormProcessor)($request);
            $statusCode = $user ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;
            $data = $user ?? $error;

            if ($statusCode !== 200) {
                return View::create($data, $statusCode);
            } else {

                $resetPasswordEmailSend = $this->processSendingPasswordResetEmail(
                    $user,
                    $mailer,
                    $translator
                );

                if ($resetPasswordEmailSend['statusCode'] !== 200) {
                    throw new Error(sprintf(
                        '%s - %s',
                        $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, [], 'ResetPasswordBundle'),
                        $translator->trans($resetPasswordEmailSend['message'], [], 'ResetPasswordBundle')
                    ), Response::HTTP_BAD_REQUEST);
                }

                $data = $resetPasswordEmailSend['message'];
                return View::create($data, $statusCode);
                // return JsonResponse::fromJsonString($data, $statusCode);
            }
        } catch (\Throwable $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Confirmation page after a user has requested a password reset.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     */
    // #[Route('/reset/{token}', name: 'app_reset_password')]
    #[Rest\Route('/reset/{token}', name: 'app_reset_password', methods: ['Get', 'Post'])]
    public function reset(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserResetPasswordFormProcessor $userResetPasswordFormProcessor,
        TranslatorInterface $translator,
        SerializerInterface $serializer,
        string $token = null
    ) {

        if ($request->isMethod('Get')) {
            if ($token) {
                // We store the token in session and remove it from the URL, to avoid the URL being
                // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
                $this->storeTokenInSession($token);
                dd($token);
                return $this->redirectToRoute('app_reset_password');
            } else {
                throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
            }
        } else {
            $token = $this->getTokenFromSession();

            if (null === $token) {
                throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
            }

            try {
                [$user, $error] = ($userResetPasswordFormProcessor)($request, $token);
                $statusCode = $user ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;
                $data = $user ?? $error;
                if ($statusCode !== 200) {
                    return View::create($data, $statusCode);
                } else {

                    // dd('paso');
                    $this->resetPasswordHelper->removeResetRequest($token);

                    $this->cleanSessionAfterReset();

                    $data =
                        $serializer->serialize([
                            'message' => 'The password has be Changed',
                            'statusCode' => $statusCode
                        ], 'json');
                    return JsonResponse::fromJsonString($data, $statusCode);
                    // return View::create($data, $statusCode);
                    // return JsonResponse::fromJsonString($data, $statusCode);
                }
            } catch (\Throwable $exception) {
                return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
    }

    private function processSendingPasswordResetEmail(
        object $user,
        MailerInterface $mailer,
        TranslatorInterface $translator
    ) {

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {

            return [
                'message' =>
                sprintf(
                    '%s - %s',
                    $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, [], 'ResetPasswordBundle'),
                    $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
                ),
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
            // return throw new Error(sprintf(
            //     '%s - %s',
            //     $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, [], 'ResetPasswordBundle'),
            //     $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            // ));
        }

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@mikrotic.cl', 'mikrotic'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        // $this->setTokenObjectInSession($resetToken);
        return ['message' => sprintf('The email was sent to %s', $user->getEmail()), 'statusCode' => Response::HTTP_OK];
        // return $this->redirectToRoute('app_check_email');
    }
}
