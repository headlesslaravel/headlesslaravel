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
        if ($this->argument('stack') == 'vue') {
            $this->installVueStack();
        }

        $this->call('vendor:publish', ['--tag' => 'craniums-vue', '--force' => true]);

        return 0;
    }

    /**
     * Run a Breeze Install Command.
     *
     * @return void
     */
    public function runBreezeInstall()
    {
        $command = ['php', 'artisan', 'breeze:install', 'vue'];

        (new Process($command, base_path()))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
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

        (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
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

        (new Process($command, base_path()))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

    /**
     * Run npm install.
     *
     * @return void
     */
    protected function runNPMInstall()
    {
        $command = ['npm', 'install'];

        (new Process($command, base_path()))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }
}
