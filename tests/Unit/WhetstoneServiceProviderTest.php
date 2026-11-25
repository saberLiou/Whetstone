<?php

namespace saberLiou\Whetstone\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use saberLiou\Whetstone\Tests\TestCase;
use saberLiou\Whetstone\WhetstoneServiceProvider;

class WhetstoneServiceProviderTest extends TestCase
{
    public function testItMergesTheDefaultConfig()
    {
        $this->assertSame([
            'helper' => null,
            'repository' => null,
            'service' => null,
            'presenter' => null,
            'view_composer' => null,
        ], config('whetstone.roots'));

        $this->assertSame([
            'helper' => 'Helpers',
            'repository' => 'Repositories',
            'service' => 'Services',
            'presenter' => 'Presenters',
            'view_composer' => 'Http\ViewComposers',
        ], config('whetstone.namespaces'));
    }

    public function testItRegistersAllCarveCommands()
    {
        $commands = array_keys(Artisan::all());

        foreach (['carve:helper', 'carve:presenter', 'carve:repository', 'carve:service', 'carve:view-composer'] as $command) {
            $this->assertContains($command, $commands);
        }
    }

    public function testItPublishesTheConfigFile()
    {
        $paths = ServiceProvider::pathsToPublish(WhetstoneServiceProvider::class, 'whetstone.config');

        $this->assertCount(1, $paths);
        $this->assertSame(realpath(__DIR__.'/../../config/whetstone.php'), realpath(array_keys($paths)[0]));
        $this->assertSame(config_path('whetstone.php'), array_values($paths)[0]);
    }
}
