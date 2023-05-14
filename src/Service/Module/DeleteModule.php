<?php

namespace App\Service\Module;


class DeleteModule
{
    private GetModule $getModule;
    private ModuleManager $moduleManager;

    public function __construct(GetModule $getModule, ModuleManager $moduleManager)
    {
        $this->getModule = $getModule;
        $this->moduleManager = $moduleManager;
    }

    public function __invoke(string $uuid)
    {
        $module = ($this->getModule)($uuid);
        $this->moduleManager->delete($module);
    }
}
