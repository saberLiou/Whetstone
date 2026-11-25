<?php

namespace saberLiou\Whetstone\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class RepositoryCarveCommand extends GeneratorCommand
{
    use CarveCommandTrait {
        stubReplacements as carveStubReplacements;
    }

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
     * The fully qualified class name of the model bound to the repository.
     *
     * @var string
     */
    protected $model;

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
     * Resolve the model bound to the repository, asking for it if not given.
     *
     * @param  string  $name
     * @return bool
     */
    protected function prepareCarving($name)
    {
        $model = $this->option('model');
        if (blank($model) && ! $this->input->isInteractive()) {
            $this->error('The model is required, please give it with the --model option.');
            return false;
        }
        while (blank($model)) {
            $model = $this->ask('The carving repository must bind a model, which model do you want to bind?');
        }

        $this->model = $this->qualifyModel($model);
        if (! $this->isValidClassName($this->model)) {
            $this->error('The model name ['.$model.'] is invalid.');
            return false;
        }
        return true;
    }

    /**
     * Qualify the given model name.
     *
     * A name with a leading backslash is regarded as fully qualified. Otherwise the name is
     * resolved under the application namespace, and under its "Models" namespace if the
     * application has a "Models" directory, unless it already starts with the application namespace.
     *
     * @param  string  $model
     * @return string
     */
    protected function qualifyModel($model)
    {
        $model = str_replace('/', '\\', trim($model));
        if (Str::startsWith($model, '\\')) {
            return ltrim($model, '\\');
        }

        $rootNamespace = $this->laravel->getNamespace();
        if (Str::startsWith($model, $rootNamespace)) {
            return $model;
        }
        return $rootNamespace.(is_dir(app_path('Models')) ? 'Models\\' : '').$model;
    }

    /**
     * Get the fully qualified classes imported by the stub.
     *
     * @return array
     */
    protected function importedClasses()
    {
        return [$this->model];
    }

    /**
     * Get the replacements of the placeholders in the stub.
     *
     * @param  string  $name
     * @return array
     */
    protected function stubReplacements($name)
    {
        $model = class_basename($this->model);
        $variable = lcfirst($model);

        return array_merge($this->carveStubReplacements($name), [
            'NamespacedDummyModel' => $this->model,
            'DummyModelVar' => $variable === 'this' ? 'model' : $variable,
            'DummyModel' => $model,
        ]);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite the Repository if it already exists'],
            ['model', 'm', InputOption::VALUE_REQUIRED, 'The model bound to the Repository'],
        ];
    }
}
