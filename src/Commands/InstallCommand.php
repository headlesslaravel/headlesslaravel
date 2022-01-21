<?php

namespace HeadlessLaravel\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'headless:install';

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
        $this->call('vendor:publish', ['--tag' => 'craniums-vue', '--force' => true]);

        return 0;
    }
}
