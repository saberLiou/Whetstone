<?php

namespace saberLiou\Whetstone\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class PresenterCarveCommand extends GeneratorCommand
{
    use CarveCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'carve:presenter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carve a new Presenter class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Presenter';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/presenter.stub';
    }
}
