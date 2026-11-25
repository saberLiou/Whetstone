<?php

namespace saberLiou\Whetstone\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class ServiceCarveCommand extends GeneratorCommand
{
    use CarveCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'carve:service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carve a new Service class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Service';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/service.stub';
    }
}
