<?php

namespace App\Service\Module;

use App\Form\Model\Module\ModuleDto;
use App\Form\Type\Module\ModuleFormType;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class ModuleFormProcessor
{

    private GetModule $getModule;
    private $moduleManager;
    private $formFactory;

    public function __construct(
        GetModule $getModule,
        ModuleManager $moduleManager,
        FormFactoryInterface $formFactory
    ) {
        $this->getModule = $getModule;
        $this->moduleManager = $moduleManager;
        $this->formFactory = $formFactory;
    }

    public function __invoke(Request $request, ?string $moduleUuid = null): array
    {
        $module = null;
        $moduleDto = null;

        if ($moduleUuid === null) {
            $moduleDto = ModuleDto::createEmpty();
        } else {
            $module = ($this->getModule)($moduleUuid);
            $moduleDto = ModuleDto::createFromModule($module);
        }

        $content = json_decode($request->getContent(), true);
        $form = $this->formFactory->create(ModuleFormType::class, $moduleDto);
        $form->submit($content);

        if (!$form->isSubmitted()) {
            return [null, 'Form is not submitted'];
        }

        if (!$form->isValid()) {
            return [null, $form];
        }

        if ($module === null) {

            $module = $this->moduleManager->create();
            $module->setCreatedAtAutomatically();
        } else {

            $module->setUpdatedAtAutomatically();
        }

        $module->setActive($moduleDto->isActive() == true ? true : false);
        $module->setName($moduleDto->getName());
        $module->setCode($moduleDto->getCode());

        $this->moduleManager->save($module);
        $this->moduleManager->reload($module);
        return [$module, null];
    }

    private function DateTimeTransform($date)
    {
        $dateObject = new DateTimeImmutable($date);
        return $dateObject;
    }
}
