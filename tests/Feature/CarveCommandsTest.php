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
            ->expectsOutput($type.' already exists!')
            ->assertExitCode(1);

        $this->assertSame('original', file_get_contents($path));
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItOverwritesAnExistingClassWithTheForceOption($command, $key, $type, $directory, $namespace)
    {
        $path = $this->sandbox.'/'.$directory.'/Foo.php';
        mkdir(dirname($path), 0755, true);
        file_put_contents($path, 'original');

        $this->artisan($command, ['name' => 'Foo', '--force' => true])->assertExitCode(0);

        $this->assertStringContainsString('class Foo', file_get_contents($path));
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItCarvesANestedClassWhoseNameStartsWithTheRootNamespace($command, $key, $type, $directory, $namespace)
    {
        $this->artisan($command, ['name' => 'Application/Foo'])->assertExitCode(0);

        $contents = $this->assertCarved($directory.'/Application/Foo.php');
        $this->assertStringContainsString('namespace '.$namespace.'\\Application;', $contents);
        $this->assertStringContainsString('class Foo', $contents);
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItDoesNotDuplicateTheNamespaceGivenWithSlashes($command, $key, $type, $directory, $namespace)
    {
        $this->artisan($command, ['name' => str_replace('\\', '/', $namespace).'/Foo'])->assertExitCode(0);

        $contents = $this->assertCarved($directory.'/Foo.php');
        $this->assertStringContainsString('namespace '.$namespace.';', $contents);
    }

    public function invalidNameProvider()
    {
        $names = ['empty' => '', 'path traversal' => '../../../escaped', 'hyphen' => 'Foo-Bar', 'leading digit' => '1Foo', 'reserved word' => 'List', 'reserved segment' => 'Admin/Class'];
        $cases = [];
        foreach ($this->simpleCommandProvider() as $label => $arguments) {
            foreach ($names as $reason => $name) {
                $cases[$label.' / '.$reason] = [$arguments[0], $name];
            }
        }
        $cases['repository / path traversal'] = ['carve:repository', '../../../escaped'];
        return $cases;
    }

    /**
     * @dataProvider invalidNameProvider
     */
    public function testItRejectsAnInvalidClassName($command, $name)
    {
        $parameters = ['name' => $name];
        if ($command === 'carve:repository') {
            $parameters['--model'] = 'User';
        }

        $this->artisan($command, $parameters)->assertExitCode(1);

        $this->assertSandboxUntouched();
        $this->assertFileNotExists(dirname($this->sandbox).'/escaped.php');
    }

    public function testViewComposerRejectsANameConflictingWithTheImportedView()
    {
        $this->artisan('carve:view-composer', ['name' => 'View'])->assertExitCode(1);

        $this->assertSandboxUntouched();
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItCarvesDirectlyUnderTheRootWhenTheNamespaceIsEmpty($command, $key, $type, $directory, $namespace)
    {
        config(['whetstone.namespaces.'.$key => '']);

        $this->artisan($command, ['name' => 'Foo'])->assertExitCode(0);

        $contents = $this->assertCarved('app/Foo.php');
        $this->assertStringContainsString('namespace App;', $contents);
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItIgnoresSurroundingBackslashesInTheConfiguredRootAndNamespace($command, $key, $type, $directory, $namespace)
    {
        config([
            'whetstone.roots.'.$key => '\\App\\',
            'whetstone.namespaces.'.$key => '\\Things\\',
        ]);

        $this->artisan($command, ['name' => 'Foo'])->assertExitCode(0);

        $contents = $this->assertCarved('app/Things/Foo.php');
        $this->assertStringContainsString('namespace App\\Things;', $contents);
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItFallsBackToTheDefaultsForMissingConfigKeys($command, $key, $type, $directory, $namespace)
    {
        // A published config that only lists another key replaces the whole array (shallow merge).
        config(['whetstone.roots' => ['other' => 'Other'], 'whetstone.namespaces' => ['other' => 'Others']]);

        $this->artisan($command, ['name' => 'Foo'])->assertExitCode(0);

        $contents = $this->assertCarved($directory.'/Foo.php');
        $this->assertStringContainsString('namespace '.$namespace.';', $contents);
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItUsesTheApplicationNamespaceByDefault($command, $key, $type, $directory, $namespace)
    {
        $this->setAutoloadPsr4(['Acme\\' => 'app/']);
        config(['whetstone' => require __DIR__.'/../../config/whetstone.php']);

        $this->artisan($command, ['name' => 'Foo'])->assertExitCode(0);

        $contents = $this->assertCarved($directory.'/Foo.php');
        $this->assertStringContainsString('namespace Acme'.substr($namespace, 3).';', $contents);
    }

    /**
     * @dataProvider simpleCommandProvider
     */
    public function testItDoesNotNeedTheApplicationNamespaceWhenTheRootIsConfigured($command, $key, $type, $directory, $namespace)
    {
        // The application directory exists but is not mapped by PSR-4, so its namespace cannot be detected.
        mkdir($this->sandbox.'/app');
        $this->setAutoloadPsr4(['Domain\\' => 'src/']);
        config(['whetstone.roots.'.$key => 'Domain', 'whetstone.namespaces.'.$key => 'Things']);

        $this->artisan($command, ['name' => 'Foo'])->assertExitCode(0);

        $this->assertCarved('src/Things/Foo.php');
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
            ->expectsOutput('Repository already exists!')
            ->assertExitCode(1);

        $this->assertSame('original', file_get_contents($path));
    }

    public function testRepositoryFailsInsteadOfAskingWhenNotInteractive()
    {
        $this->artisan('carve:repository', ['name' => 'PostRepository', '--no-interaction' => true])
            ->expectsOutput('The model is required, please give it with the --model option.')
            ->assertExitCode(1);

        $this->assertSandboxUntouched();
    }

    /**
     * [model option, expected import, expected short name, expected variable]
     */
    public function modelProvider()
    {
        return [
            'relative with slashes' => ['Models/User', 'App\\Models\\User', 'User', 'user'],
            'relative with backslashes' => ['Models\\User', 'App\\Models\\User', 'User', 'user'],
            'qualified by the application namespace' => ['App\\Models\\User', 'App\\Models\\User', 'User', 'user'],
            'absolute' => ['\\Domain\\Models\\User', 'Domain\\Models\\User', 'User', 'user'],
            'named this' => ['This', 'App\\This', 'This', 'model'],
        ];
    }

    /**
     * @dataProvider modelProvider
     */
    public function testRepositoryResolvesTheGivenModel($model, $import, $short, $variable)
    {
        $this->artisan('carve:repository', ['name' => 'FooRepository', '--model' => $model])->assertExitCode(0);

        $contents = $this->assertCarved('app/Repositories/FooRepository.php');
        $this->assertStringContainsString('use '.$import.';', $contents);
        $this->assertStringContainsString('protected $'.$variable.';', $contents);
        $this->assertStringContainsString('public function __construct('.$short.' $'.$variable.')', $contents);
        $this->assertStringContainsString('$this->'.$variable.' = $'.$variable.';', $contents);
        $this->assertStringNotContainsString('Dummy', $contents);
    }

    public function testRepositoryResolvesTheModelIntoTheModelsDirectoryWhenItExists()
    {
        mkdir($this->sandbox.'/app/Models', 0755, true);

        $this->artisan('carve:repository', ['name' => 'FooRepository', '--model' => 'User'])->assertExitCode(0);

        $contents = $this->assertCarved('app/Repositories/FooRepository.php');
        $this->assertStringContainsString('use App\\Models\\User;', $contents);
    }

    public function testRepositoryRejectsAnInvalidModel()
    {
        $this->artisan('carve:repository', ['name' => 'FooRepository', '--model' => 'Foo-Bar'])->assertExitCode(1);

        $this->assertSandboxUntouched();
    }

    public function testRepositoryRejectsANameConflictingWithTheModel()
    {
        $this->artisan('carve:repository', ['name' => 'User', '--model' => 'User'])->assertExitCode(1);

        $this->assertSandboxUntouched();
    }
}
