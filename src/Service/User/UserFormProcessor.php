<?php

namespace App\Service\User;

use App\Form\Model\User\UserDto;
use App\Form\Type\User\UserFormType;
use App\Service\FileManager\FileDeleter;
use App\Service\FileManager\FileUploader;
use App\Service\RoleGroup\GetRoleGroup;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFormProcessor
{

    private GetUser $getUser;
    private GetRoleGroup $getRoleGroup;
    private $userManager;
    private $formFactory;
    private UserPasswordHasherInterface $userPasswordHasher;
    private FileUploader $fileUploader;
    private FileDeleter $fileDeleter;

    public function __construct(
        GetUser $getUser,
        GetRoleGroup $getRoleGroup,
        UserManager $userManager,
        FormFactoryInterface $formFactory,
        UserPasswordHasherInterface $userPasswordHasher,
        FileUploader $fileUploader,
        FileDeleter $fileDeleter
    ) {
        $this->getUser = $getUser;
        $this->getRoleGroup = $getRoleGroup;
        $this->userManager = $userManager;
        $this->formFactory = $formFactory;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->fileUploader = $fileUploader;
        $this->fileDeleter = $fileDeleter;
    }

    public function __invoke(Request $request, ?string $userUuid = null): array
    {
        $user = null;
        $userDto = null;

        if ($userUuid === null) {
            $userDto = UserDto::createEmpty();
        } else {
            $user = ($this->getUser)($userUuid);
            $userDto = UserDto::createFromUser($user);
        }

        $content = json_decode($request->getContent(), true);
        $form = $this->formFactory->create(UserFormType::class, $userDto);
        $form->submit($content);

        if (!$form->isSubmitted()) {
            return [null, 'Form is not submitted'];
        }

        if (!$form->isValid()) {
            return [null, $form];
        }

        $roleGroupsDto = [];

        foreach ($userDto->getRoleGroups() as $newRoleGroupDto) {
            // $roleGroup = null;
            // dd($newRoleGroupDto);
            if ($newRoleGroupDto->getId() !== null) {
                $roleGroup = ($this->getRoleGroup)($newRoleGroupDto->getUuid());
                $roleGroupsDto[] = $roleGroup;
            }
            // array_push($roleGroupsDto, $roleGroup);
        }


        if ($user === null) {
            // dd($userDto->getPassword());
            $user = $this->userManager->create();

            if ($userDto->getBase64File()) {
                $filename = $this->fileUploader->getFileNameFromBase64File($userDto->getBase64File(), 'user_image');
                $user->setAvatar($filename);
                $this->fileUploader->uploadBase64File($filename, $userDto->getBase64File(), 'user_image');
            }

            $user->setPassword($this->userPasswordHasher->hashPassword(
                $user,
                $userDto->getPassword()
            ));

            foreach ($roleGroupsDto as $roleGroupDto) {
                $user->addRoleGroup($roleGroupDto);
            }

            $user->setCreatedAtAutomatically();
        } else {

            if ($userDto->getBase64File()) {
                $fileNameOld = $user->getAvatar();
                ($this->fileDeleter)($fileNameOld);
                // $this->fileUploader->deleteFile($fileNameOld);

                $filename = $this->fileUploader->getFileNameFromBase64File($userDto->getBase64File(), 'user_image');
                $user->setAvatar($filename);
                $this->fileUploader->uploadBase64File($filename, $userDto->getBase64File(), 'user_image');
            }

            foreach ($user->getRoleGroups() as $roleGroup) {
                if (!in_array($roleGroup, $roleGroupsDto, true)) {
                    $user->removeRoleGroup($roleGroup);
                }
            }

            foreach ($roleGroupsDto as $roleGroupDto) {

                if (!$user->hasRoleGroup($roleGroupDto)) {
                    $user->addRoleGroup($roleGroupDto);
                }
            }

            $user->setUpdatedAtAutomatically();
        }


        $user->setEmail($userDto->getEmail());
        // $user->setRoles($userDto->getRoles());
        // $user->setRoleGroups($userDto->getRoleGroups());
        $user->setFirstName($userDto->getFirstName());
        $user->setLastName($userDto->getLastName());
        $user->setDni($userDto->getDni());

        if ($userDto->getExpiredAt()) {

            $user->setExpiredAt(new CarbonImmutable($userDto->getExpiredAt()));
        }

        $user->setActive($userDto->isActive() == true ? true : false);

        $this->userManager->save($user);


        $this->userManager->reload($user);

        return [$user, null];
    }

    private function DateTimeTransform($date)
    {
        $dateObject = new DateTimeImmutable($date);
        return $dateObject;
    }
}
