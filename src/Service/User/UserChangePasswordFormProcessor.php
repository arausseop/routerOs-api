<?php

namespace App\Service\User;

use App\Form\Model\User\UserChangePasswordDto;
use App\Form\Type\User\UserChangePasswordFormType;
use App\Service\FileManager\FileDeleter;
use App\Service\FileManager\FileUploader;
use App\Service\RoleGroup\GetRoleGroup;
use Error;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

class UserChangePasswordFormProcessor
{

    private GetUser $getUser;
    private $userManager;
    private $formFactory;
    private UserPasswordHasherInterface $userPasswordHasher;
    private TranslatorInterface $translator;

    public function __construct(
        GetUser $getUser,
        UserManager $userManager,
        FormFactoryInterface $formFactory,
        UserPasswordHasherInterface $userPasswordHasher,
        TranslatorInterface $translator,
    ) {
        $this->getUser = $getUser;
        $this->userManager = $userManager;
        $this->formFactory = $formFactory;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, ?string $userUuid = null): array
    {

        $user = null;
        $userResetPasswordDto = null;

        if ($userUuid === null) {
            $userResetPasswordDto = UserChangePasswordDto::createEmpty();
        } else {
            $user = ($this->getUser)($userUuid);
            $userResetPasswordDto = UserChangePasswordDto::createFromUser($user);
        }

        $content = json_decode($request->getContent(), true);
        $form = $this->formFactory->create(UserChangePasswordFormType::class, $userResetPasswordDto);
        $form->submit($content);
        if (!$form->isSubmitted()) {
            return [null, 'Form is not submitted'];
        }

        if (!$form->isValid()) {
            return [null, $form];
        }

        if ($user === null) {
            try {
                $user = ($this->getUser)($userUuid);
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
