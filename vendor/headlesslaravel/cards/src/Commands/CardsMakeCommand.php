<?php

namespace HeadlessLaravel\Cards\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class CardsMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:cards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new list request class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Cards';

    public function handle()
    {
        parent::handle();

        $this->info('Add the following to your routes file:');
        $this->info("Route::cards('dashboard', \App\Http\Cards\Dashboard::class);");
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('cards.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim("stubs/$stub", '/')))
            ? $customPath
            : __DIR__.'/../../stubs/'.$stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Cards';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
        ];
    }
}
