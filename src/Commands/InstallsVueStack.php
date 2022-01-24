<?php

namespace HeadlessLaravel\Commands;

trait InstallsVueStack
{
    public function installVueStack()
    {
        // composer require laravel/breeze
        $this->requireComposerPackages('laravel/breeze:^1.7');

        // Install Breeze Vue Stack
        $this->runBreezeInstall();

        // NPM Packages...
        $this->updateNodePackages(function ($packages) {
            return [
                '@craniums/vue' => '^0.0.11',
            ] + $packages;
        });

        // Update resources/js/app.js
        $this->updateAppJs();

        // Update tailwind.config.js
        $this->updateTailwindConfig();

        // run NPM Install and NPM PROD
        $this->runNPMInstall();
        $this->runNPMProd();
    }

    protected function updateAppJs()
    {
        $addImportAfter = "import { InertiaProgress } from '@inertiajs/progress';";
        $importCranium = "import Cranium from '@craniums/vue';";
        $this->addLineAfter($addImportAfter, $importCranium, resource_path('js/app.js'));

        $addUseAfter = '.use(plugin)';
        $useCranium = '            .use(Cranium)';
        $this->addLineAfter($addUseAfter, $useCranium, resource_path('js/app.js'));
    }

    protected function updateTailwindConfig()
    {
        $addTailwindContentAfter = "'./resources/js/**/*.vue',";
        $tailwindContent = "        './node_modules/@craniums/vue/src/**/*.vue',";
        $this->addLineAfter($addTailwindContentAfter, $tailwindContent, base_path('tailwind.config.js'));
    }
}
