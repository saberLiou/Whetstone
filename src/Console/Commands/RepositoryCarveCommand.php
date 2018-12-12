<?php

namespace saberLiou\Whetstone\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;

class RepositoryCarveCommand extends GeneratorCommand
{
    use CarveCommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carve:repository {name} {--model=}';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'carve:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carve a new Repository class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/repository.stub';
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $name = $this->refineConflictNameForNamespace($this->qualifyClass($this->getNameInput()), 'repository');
        $path = $this->getPath($name);
        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((! $this->hasOption('force') || ! $this->option('force')) && $this->alreadyExists($name)) {
            $this->error($this->type.' already exists!');
            return false;
        }
        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);
        $model = $this->option('model');
        while (blank($model)) {
            $model = $this->ask('The carving repository must bind a model, which model do you want to bind?');
        }
        $this->files->put($path, $this->buildRepository($name, $model));
        $this->info($this->type.' carved successfully.');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $childNamespace = config('whetstone.namespaces.repository', 'Repositories');
        return $rootNamespace.(blank($childNamespace) ? '' : '\\'.$childNamespace);
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);
        return base_path($this->rootNamespace()).str_replace('\\', '/', $name).'.php';
    }

    /**
     * Build the class of the repository with the given name and model.
     *
     * @param  string  $name
     * @param  string  $model
     * @return string
     */
    protected function buildRepository($name, $model)
    {
        $stub = $this->files->get($this->getStub());
        return $this->replaceNamespace($stub, $name)->replaceModelAndVar($stub, $model)->replaceClass($stub, $name);
    }

    /**
     * Replace the model name (and its variable name) of the repository for the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return $this
     */
    protected function replaceModelAndVar(&$stub, $model)
    {
        $stub = str_replace('DummyModel', $model, str_replace('DummyModelVar', lcfirst($model), $stub));
        return $this;
    }

    /**
     * Replace the class name of the repository for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);
        return str_replace('DummyRepository', $class, $stub);
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return config('whetstone.roots.repository', $this->laravel->getNamespace());
    }
}