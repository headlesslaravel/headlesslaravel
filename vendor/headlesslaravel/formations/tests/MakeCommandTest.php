<?php

namespace HeadlessLaravel\Formations\Tests;

use Illuminate\Support\Facades\File;

class MakeCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        app()->setBasePath(__DIR__.'/App');
        mkdir(__DIR__.'/App');
        file_put_contents(__DIR__.'/App/composer.json', json_encode([
            'autoload' => [
                'psr-4' => [
                    'Testing\\' => realpath(base_path()),
                ],
            ],
        ]));
    }

    public function tearDown(): void
    {
        File::deleteDirectory(__DIR__.'/App');

        parent::tearDown();
    }

    public function test_make_command()
    {
        $this->artisan('make:formation ArticleFormation');
        $this->assertTrue(file_exists(base_path('app/Formations/ArticleFormation.php')));
    }

    public function test_make_command_custom_stub()
    {
        mkdir(base_path('stubs'));
        file_put_contents(base_path('stubs/formation.stub'), 'hello');
        $this->artisan('make:formation ArticleFormation');
        $this->assertTrue(file_exists(base_path('app/Formations/ArticleFormation.php')));
        $this->assertEquals('hello', file_get_contents(base_path('app/Formations/ArticleFormation.php')));
    }
}
