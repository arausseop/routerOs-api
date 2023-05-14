<?php

namespace App\Service\User\ResetPassword;

use App\Form\Model\User\UserResetPasswordDto;
use App\Form\Type\User\UserFormType;
use App\Form\Type\User\UserResetPasswordFormType;
use App\Form\Type\User\UserResetPasswordRequestFormType;
use App\Model\Exception\User\UserNotFound;
use App\Service\RoleGroup\GetRoleGroup;
use App\Service\User\GetUser;
use App\Service\User\UserManager;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Error;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class UserResetPasswordFormProcessor
{

    private GetUser $getUser;
    private GetRoleGroup $getRoleGroup;
    private $userManager;
    private $formFactory;
    private UserPasswordHasherInterface $userPasswordHasher;
    private ResetPasswordHelperInterface $resetPasswordHelper;
    private TranslatorInterface $translator;

    public function __construct(
        GetUser $getUser,
        GetRoleGroup $getRoleGroup,
        UserManager $userManager,
        FormFactoryInterface $formFactory,
        UserPasswordHasherInterface $userPasswordHasher,
        ResetPasswordHelperInterface $resetPasswordHelper,
        TranslatorInterface $translator,
    ) {
        $this->getUser = $getUser;
        $this->getRoleGroup = $getRoleGroup;
        $this->userManager = $userManager;
        $this->formFactory = $formFactory;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, string $token, ?string $userUuid = null): array
    {
        $user = null;
        $userResetPasswordDto = null;

        if ($userUuid === null) {
            $userResetPasswordDto = UserResetPasswordDto::createEmpty();
        } else {
            $user = ($this->getUser)($userUuid);
            $userResetPasswordDto = UserResetPasswordDto::createFromUser($user);
        }

        $content = json_decode($request->getContent(), true);
        $form = $this->formFactory->create(UserResetPasswordFormType::class, $userResetPasswordDto);

        $form->submit($content);
        if (!$form->isSubmitted()) {
            return [null, 'Form is not submitted'];
        }

        if (!$form->isValid()) {
            return [null, $form];
        }

        if ($user === null) {
            //TODO: search user by token or get from controller
            try {
                $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
                $encodedPassword = $this->userPasswordHasher->hashPassword(
                    $user,
                    $userResetPasswordDto->getPlainPassword()
                );

                $user->setPassword($encodedPassword);
                $user->setUpdatedAtAutomatically();
            } catch (ResetPasswordExceptionInterface $e) {
                throw new Error(sprintf(
                    '%s - %s',
                    $this->translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                    $this->translator->trans($e->getReason(), [], 'ResetPasswordBundle')
                ), Response::HTTP_BAD_REQUEST);
            }
        }

        $this->userManager->save($user);
        $this->userManager->reload($user);
        return [$user, null];
    }
}
