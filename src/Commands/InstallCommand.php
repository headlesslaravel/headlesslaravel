<?php

namespace HeadlessLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    use InstallsVueStack;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'headless:install {stack=vue : The development stack that should be installed (blade,vue,api)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install headless laravel assets';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->callSilent('vendor:publish', ['--tag' => 'headless-setup', '--force' => true]);

        if ($this->argument('stack') == 'vue') {
            $this->installVueStack();
            $this->callSilent('vendor:publish', ['--tag' => 'craniums-vue', '--force' => true]);
        }

        $this->updateDatabaseSeeder();

        $this->warn('todo: php artisan migrate:fresh --seed');
        $this->info('user: admin@example.com pass: password');

        return 0;
    }

    /**
     * Update the database seeder.
     *
     * @return void
     */
    public function updateDatabaseSeeder()
    {
        $this->replaceLine(
            '// \App\Models\User::factory(10)->create();',
            "\App\Models\User::factory()->create(['email' => 'admin@example.com']);\n\t\t\App\Models\User::factory(10)->create()",
            database_path('seeders/DatabaseSeeder.php')
        );
    }

    /**
     * Run a Breeze Install Command.
     *
     * @return void
     */
    public function runBreezeInstall()
    {
        $command = ['php', 'artisan', 'breeze:install', 'vue'];

        $this->info('- Running... breeze:install');

        (new Process($command, base_path()))
            ->setTimeout(null)
            ->run();
    }

    /**
     * Installs the given Composer Packages into the application.
     *
     * @param mixed $packages
     *
     * @return void
     */
    protected function requireComposerPackages($packages)
    {
        $command = array_merge(
            ['composer', 'require'],
            is_array($packages) ? $packages : func_get_args()
        );

        $this->info('- Running... composer require');

        (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run();
    }

    /**
     * Update the "package.json" file.
     *
     * @param callable $callback
     * @param bool     $dev
     *
     * @return void
     */
    protected static function updateNodePackages(callable $callback, $dev = true)
    {
        if (!file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );
    }

    /**
     * Replace Line.
     *
     * @param string $addAfter
     * @param string $string
     * @param string $path
     *
     * @return void
     */
    protected function replaceLine($search, $replace, $path)
    {
        $fileContents = file_get_contents($path);

        $fileContents = Str::replaceFirst($search, $replace, $fileContents);

        file_put_contents($path, $fileContents);
    }

    /**
     * Add a given string within a given file after specific string.
     *
     * @param string $addAfter
     * @param string $string
     * @param string $path
     *
     * @return void
     */
    protected function addLineAfter($addAfter, $string, $path)
    {
        $fileContents = file_get_contents($path);

        $fileContents = Str::replaceFirst($addAfter, $addAfter.PHP_EOL.$string, $fileContents);

        file_put_contents($path, $fileContents);
    }

    /**
     * Run npm run prod.
     *
     * @return void
     */
    protected function runNPMProd()
    {
        $command = ['npm', 'run', 'prod'];

        $this->info('- Running... npm run prod');

        (new Process($command, base_path()))
            ->setTimeout(null)
            ->run();
    }

    /**
     * Run npm install.
     *
     * @return void
     */
    protected function runNPMInstall()
    {
        $command = ['npm', 'install'];

        $this->info('- Running... npm install');

        (new Process($command, base_path()))
            ->setTimeout(null)
            ->run();
    }
}
