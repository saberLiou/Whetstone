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
     * Every path segment is compared case-sensitively against the directory listing, so that
     * the assertion also fails on case-insensitive filesystems (e.g. macOS) when the case differs.
     *
     * @param  string  $relativePath
     * @return string
     */
    protected function assertCarved($relativePath)
    {
        $directory = $this->sandbox;
        foreach (explode('/', $relativePath) as $segment) {
            $this->assertContains(
                $segment,
                is_dir($directory) ? scandir($directory) : [],
                "Expected [{$relativePath}] to be carved, but [{$segment}] does not exist (case-sensitive) in [{$directory}]."
            );
            $directory .= '/'.$segment;
        }

        return file_get_contents($this->sandbox.'/'.$relativePath);
    }

    /**
     * Assert that nothing but the composer.json was written into the sandbox.
     *
     * @return void
     */
    protected function assertSandboxUntouched()
    {
        $this->assertSame(['composer.json'], array_values(array_diff(scandir($this->sandbox), ['.', '..'])));
    }

    /**
     * Replace the PSR-4 autoload map of the sandbox application's composer.json.
     *
     * @param  array  $psr4
     * @return void
     */
    protected function setAutoloadPsr4(array $psr4)
    {
        file_put_contents($this->sandbox.'/composer.json', json_encode([
            'autoload' => ['psr-4' => $psr4],
        ]));

        // Application::getNamespace() caches the namespace it detected.
        $namespace = new \ReflectionProperty($this->app, 'namespace');
        $namespace->setAccessible(true);
        $namespace->setValue($this->app, null);
    }
}
