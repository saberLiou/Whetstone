<?php

namespace saberLiou\Whetstone\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;

class ViewComposerCarveCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'carve:view-composer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carve a new ViewComposer class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'ViewComposer';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/view_composer.stub';
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($name);
        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((! $this->hasOption('force') || ! $this->option('force')) && $this->alreadyExists($this->getNameInput())) {
            $this->error($this->type.' already exists!');
            return false;
        }
        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);
        $this->files->put($path, $this->buildClass($name));
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
        $childNamespace = config('whetstone.namespaces.view_composer', 'ViewComposers');
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
     * Replace the class name of the view composer for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);
        return str_replace('DummyViewComposer', $class, $stub);
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return config('whetstone.roots.view_composer', $this->laravel->getNamespace());
    }
}