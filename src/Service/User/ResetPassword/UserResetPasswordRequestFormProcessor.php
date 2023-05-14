<?php

namespace App\Service\User\ResetPassword;

use App\Form\Model\User\UserResetPasswordDto;
use App\Form\Type\User\UserFormType;
use App\Form\Type\User\UserResetPasswordRequestFormType;
use App\Model\Exception\User\UserNotFound;
use App\Service\RoleGroup\GetRoleGroup;
use App\Service\User\GetUser;
use App\Service\User\UserManager;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserResetPasswordRequestFormProcessor
{

    private GetUser $getUser;
    private GetRoleGroup $getRoleGroup;
    private $userManager;
    private $formFactory;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(
        GetUser $getUser,
        GetRoleGroup $getRoleGroup,
        UserManager $userManager,
        FormFactoryInterface $formFactory,
        UserPasswordHasherInterface $userPasswordHasher
    ) {
        $this->getUser = $getUser;
        $this->getRoleGroup = $getRoleGroup;
        $this->userManager = $userManager;
        $this->formFactory = $formFactory;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function __invoke(Request $request, ?string $userUuid = null): array
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
        $form = $this->formFactory->create(UserResetPasswordRequestFormType::class, $userResetPasswordDto);
        $form->submit($content);

        if (!$form->isSubmitted()) {
            return [null, 'Form is not submitted'];
        }

        if (!$form->isValid()) {
            return [null, $form];
        }

        if ($user === null) {
            if (!$userResetPasswordDto->getEmail()) {
                return [null, 'Email not recived'];
            }
            $user = $this->userManager->getRepository()->findOneByEmail($userResetPasswordDto->getEmail());
            if (!$user) {
                UserNotFound::throwException();
            }
        }

        return [$user, null];
    }
}
