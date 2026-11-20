<?php

namespace saberLiou\Whetstone\Tests;

use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase as Orchestra;
use saberLiou\Whetstone\WhetstoneServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * The temporary base path that carved classes are written into.
     *
     * @var string
     */
    protected $sandbox;

    protected function setUp(): void
    {
        $this->sandbox = sys_get_temp_dir().'/whetstone-tests-'.uniqid();
        (new Filesystem)->makeDirectory($this->sandbox, 0755, true);
        // Application::getNamespace() reads the PSR-4 map from the base path's composer.json.
        file_put_contents($this->sandbox.'/composer.json', json_encode([
            'autoload' => ['psr-4' => ['App\\' => 'app/']],
        ]));

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem)->deleteDirectory($this->sandbox);
    }

    protected function getPackageProviders($app)
    {
        return [WhetstoneServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->setBasePath($this->sandbox);
    }

    /**
     * Assert that a file was carved at the given path relative to the sandbox, and return its contents.
     *
     * @param  string  $relativePath
     * @return string
     */
    protected function assertCarved($relativePath)
    {
        $path = $this->sandbox.'/'.$relativePath;
        $this->assertFileExists($path);

        return file_get_contents($path);
    }
}
