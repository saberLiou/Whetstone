<?php

namespace saberLiou\Whetstone\Tests\Unit;

use saberLiou\Whetstone\Console\Commands\CarveCommandTrait;
use saberLiou\Whetstone\Tests\TestCase;

class CarveCommandTraitTest extends TestCase
{
    use CarveCommandTrait;

    /**
     * The application that the trait resolves the application namespace from, as in a console command.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $laravel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->laravel = $this->app;
    }

    public function testItPrependsTheConfiguredNamespaceWhenMissing()
    {
        $this->assertSame(
            'App\\Services\\App\\UserService',
            $this->refineConflictNameForNamespace('App\\UserService', 'service')
        );
    }

    public function testItKeepsANameThatAlreadyStartsWithTheConfiguredNamespace()
    {
        $this->assertSame(
            'App\\Services\\UserService',
            $this->refineConflictNameForNamespace('App\\Services\\UserService', 'service')
        );
    }

    public function testItKeepsNestedNamesUnderTheConfiguredNamespace()
    {
        $this->assertSame(
            'App\\Http\\ViewComposers\\Admin\\NavComposer',
            $this->refineConflictNameForNamespace('App\\Http\\ViewComposers\\Admin\\NavComposer', 'view_composer')
        );
    }

    public function testItUsesCustomisedRootsAndNamespaces()
    {
        config([
            'whetstone.roots.helper' => 'Domain',
            'whetstone.namespaces.helper' => 'Support',
        ]);

        $this->assertSame(
            'Domain\\Support\\StrHelper',
            $this->refineConflictNameForNamespace('Domain\\Support\\StrHelper', 'helper')
        );
        $this->assertSame(
            'Domain\\Support\\Domain\\StrHelper',
            $this->refineConflictNameForNamespace('Domain\\StrHelper', 'helper')
        );
    }

    public function testItFallsBackToTheDefaultNamespaceWhenTheConfigKeyIsMissing()
    {
        config(['whetstone.roots' => ['service' => 'App'], 'whetstone.namespaces' => []]);

        $this->assertSame(
            'App\\Services\\UserService',
            $this->refineConflictNameForNamespace('UserService', 'service')
        );
    }
}
