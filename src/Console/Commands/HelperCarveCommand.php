<?php

namespace saberLiou\Whetstone\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;

class HelperCarveCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'carve:helper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carve a new Helper class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Helper';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/helper.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\'.config('whetstone.namespaces.helper', 'Helpers');
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
        return base_path(config('whetstone.roots.helper', $this->laravel->getNamespace())).str_replace('\\', '/', $name).'.php';
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return config('whetstone.roots.helper', $this->laravel->getNamespace());
    }
}