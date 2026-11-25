<?php

namespace saberLiou\Whetstone\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class ViewComposerCarveCommand extends GeneratorCommand
{
    use CarveCommandTrait;

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
     * Get the fully qualified classes imported by the stub.
     *
     * @return array
     */
    protected function importedClasses()
    {
        return [\Illuminate\View\View::class];
    }
}
