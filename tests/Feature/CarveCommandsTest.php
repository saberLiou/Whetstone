<?php

namespace saberLiou\Whetstone\Tests\Feature;

use saberLiou\Whetstone\Tests\TestCase;

class CarveCommandsTest extends TestCase
{
    /**
     * [command, config key, type, default directory, default namespace]
     */
    public function simpleCommandProvider()
    {
        return [
            'helper' => ['carve:helper', 'helper', 'Helper', 'app/Helpers', 'App\\Helpers'],
            'presenter' => ['carve:presenter', 'presenter', 'Presenter', 'app/Presenters', 'App\\Presenters'],
            'service' => ['carve:service', 'service', 'Service', 'app/Services', 'App\\Services'],
            'view composer' => ['carve:view-composer', 'view_composer', 'ViewComposer', 'app/Http/ViewComposers', 'App\\Http\\ViewComposers'],
        ];
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItCarvesAClassIntoTheDefaultNamespace($command, $key, $type, $directory, $namespace)
    {
        $this->artisan($command, ['name' => 'Foo'])
            ->expectsOutput($type.' carved successfully.')
            ->assertExitCode(0);

        $contents = $this->assertCarved($directory.'/Foo.php');
        $this->assertStringContainsString('namespace '.$namespace.';', $contents);
        $this->assertStringContainsString('class Foo', $contents);
        $this->assertStringNotContainsString('Dummy', $contents);
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItCarvesANestedClass($command, $key, $type, $directory, $namespace)
    {
        $this->artisan($command, ['name' => 'Admin/Foo'])->assertExitCode(0);

        $contents = $this->assertCarved($directory.'/Admin/Foo.php');
        $this->assertStringContainsString('namespace '.$namespace.'\\Admin;', $contents);
        $this->assertStringContainsString('class Foo', $contents);
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItDoesNotDuplicateTheNamespaceWhenTheNameAlreadyContainsIt($command, $key, $type, $directory, $namespace)
    {
        $this->artisan($command, ['name' => $namespace.'\\Foo'])->assertExitCode(0);

        $contents = $this->assertCarved($directory.'/Foo.php');
        $this->assertStringContainsString('namespace '.$namespace.';', $contents);
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItRefusesToOverwriteAnExistingClass($command, $key, $type, $directory, $namespace)
    {
        $path = $this->sandbox.'/'.$directory.'/Foo.php';
        mkdir(dirname($path), 0755, true);
        file_put_contents($path, 'original');

        $this->artisan($command, ['name' => 'Foo'])
            ->expectsOutput($type.' already exists!');

        $this->assertSame('original', file_get_contents($path));
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItCarvesIntoTheDirectoryMappedByPsr4ForACustomisedRoot($command, $key, $type, $directory, $namespace)
    {
        $this->setAutoloadPsr4(['App\\' => 'app/', 'Domain\\' => 'src/domain/']);
        config([
            'whetstone.roots.'.$key => 'Domain',
            'whetstone.namespaces.'.$key => 'Custom\\Things',
        ]);

        $this->artisan($command, ['name' => 'Foo'])->assertExitCode(0);

        $contents = $this->assertCarved('src/domain/Custom/Things/Foo.php');
        $this->assertStringContainsString('namespace Domain\\Custom\\Things;', $contents);
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItCarvesIntoASubdirectoryForARootNestedUnderAPsr4Namespace($command, $key, $type, $directory, $namespace)
    {
        config([
            'whetstone.roots.'.$key => 'App\\Modules',
            'whetstone.namespaces.'.$key => 'Things',
        ]);

        $this->artisan($command, ['name' => 'Foo'])->assertExitCode(0);

        $contents = $this->assertCarved('app/Modules/Things/Foo.php');
        $this->assertStringContainsString('namespace App\\Modules\\Things;', $contents);
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItFallsBackToTheRootNamespaceAsDirectoryWhenNotMappedByPsr4($command, $key, $type, $directory, $namespace)
    {
        config([
            'whetstone.roots.'.$key => 'Domain',
            'whetstone.namespaces.'.$key => 'Custom\\Things',
        ]);

        $this->artisan($command, ['name' => 'Foo'])->assertExitCode(0);

        $contents = $this->assertCarved('Domain/Custom/Things/Foo.php');
        $this->assertStringContainsString('namespace Domain\\Custom\\Things;', $contents);
    }

    public function testRepositoryIsCarvedWithTheGivenModel()
    {
        $this->artisan('carve:repository', ['name' => 'UserRepository', '--model' => 'User'])
            ->expectsOutput('Repository carved successfully.')
            ->assertExitCode(0);

        $contents = $this->assertCarved('app/Repositories/UserRepository.php');
        $this->assertStringContainsString('namespace App\\Repositories;', $contents);
        $this->assertStringContainsString('use App\\User;', $contents);
        $this->assertStringContainsString('class UserRepository', $contents);
        $this->assertStringContainsString('protected $user;', $contents);
        $this->assertStringContainsString('public function __construct(User $user)', $contents);
        $this->assertStringContainsString('$this->user = $user;', $contents);
        $this->assertStringNotContainsString('Dummy', $contents);
    }

    public function testRepositoryAsksForAModelWhenNoneIsGiven()
    {
        $this->artisan('carve:repository', ['name' => 'PostRepository'])
            ->expectsQuestion('The carving repository must bind a model, which model do you want to bind?', 'Post')
            ->expectsOutput('Repository carved successfully.')
            ->assertExitCode(0);

        $contents = $this->assertCarved('app/Repositories/PostRepository.php');
        $this->assertStringContainsString('public function __construct(Post $post)', $contents);
    }

    public function testRepositoryRefusesToOverwriteAnExistingClass()
    {
        $path = $this->sandbox.'/app/Repositories/UserRepository.php';
        mkdir(dirname($path), 0755, true);
        file_put_contents($path, 'original');

        $this->artisan('carve:repository', ['name' => 'UserRepository', '--model' => 'User'])
            ->expectsOutput('Repository already exists!');

        $this->assertSame('original', file_get_contents($path));
    }
}
