<?php

namespace saberLiou\Whetstone\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

trait CarveCommandTrait
{
    /**
     * The default child namespaces under the root namespace, keyed by the config key.
     *
     * @var array
     */
    protected static $defaultNamespaces = [
        'helper' => 'Helpers',
        'repository' => 'Repositories',
        'service' => 'Services',
        'presenter' => 'Presenters',
        'view_composer' => 'Http\ViewComposers',
    ];

    /**
     * Words that cannot be used as a class name or a namespace segment.
     *
     * @var array
     */
    protected static $reservedWords = [
        '__halt_compiler', 'abstract', 'and', 'array', 'as', 'bool', 'break', 'callable', 'case', 'catch',
        'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif',
        'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'enum', 'eval',
        'exit', 'extends', 'false', 'final', 'finally', 'float', 'fn', 'for', 'foreach', 'function', 'global',
        'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'int', 'interface',
        'isset', 'iterable', 'list', 'match', 'mixed', 'namespace', 'never', 'new', 'null', 'object', 'or',
        'parent', 'print', 'private', 'protected', 'public', 'readonly', 'require', 'require_once', 'return',
        'self', 'static', 'string', 'switch', 'throw', 'trait', 'true', 'try', 'unset', 'use', 'var', 'void',
        'while', 'xor', 'yield',
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->refineConflictNameForNamespace($this->getNameInput(), $this->configKey());
        if (! $this->isValidClassName($name)) {
            $this->error('The class name ['.$this->getNameInput().'] is invalid.');
            return 1;
        }

        // Do not overwrite the user's code unless the --force option is given.
        $path = $this->getPath($name);
        if (! $this->option('force') && $this->files->exists($path)) {
            $this->error($this->type.' already exists!');
            return 1;
        }

        if (! $this->prepareCarving($name)) {
            return 1;
        }

        $class = class_basename($name);
        foreach ($this->importedClasses() as $imported) {
            if (strcasecmp($class, class_basename($imported)) === 0) {
                $this->error("The class name [{$class}] conflicts with the imported class [{$imported}].");
                return 1;
            }
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->buildClass($name));
        $this->info($this->type.' carved successfully.');
        return 0;
    }

    /**
     * Qualify the given class name with the configured root and child namespaces.
     *
     * Slashes are treated as namespace separators, and the namespaces are prepended unless
     * the name already starts with them.
     *
     * @param  string  $name
     * @param  string  $class  The config key of the carved class.
     * @return string
     */
    public function refineConflictNameForNamespace($name, $class)
    {
        $name = trim(str_replace('/', '\\', trim($name)), '\\');
        $namespace = $this->carvedNamespace($class);
        if (! Str::startsWith($name, $namespace.'\\')) {
            $name = $namespace.'\\'.$name;
        }
        return $name;
    }

    /**
     * Get the namespace that the classes are carved into, combined by the root and child namespaces.
     *
     * @param  string  $class  The config key of the carved class.
     * @return string
     */
    protected function carvedNamespace($class)
    {
        $namespace = config('whetstone.namespaces.'.$class);
        if (is_null($namespace)) {
            $namespace = static::$defaultNamespaces[$class];
        }
        $namespace = trim($namespace, '\\');
        return $this->carvedRootNamespace($class).(blank($namespace) ? '' : '\\'.$namespace);
    }

    /**
     * Get the configured root namespace, or the application namespace if it is not configured.
     *
     * @param  string  $class  The config key of the carved class.
     * @return string
     */
    protected function carvedRootNamespace($class)
    {
        $root = trim((string) config('whetstone.roots.'.$class), '\\');
        return blank($root) ? trim($this->laravel->getNamespace(), '\\') : $root;
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return $this->carvedRootNamespace($this->configKey());
    }

    /**
     * Determine whether every segment of the given qualified name is a valid PHP identifier.
     *
     * @param  string  $name
     * @return bool
     */
    protected function isValidClassName($name)
    {
        foreach (explode('\\', $name) as $segment) {
            if (! preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $segment)
                || in_array(strtolower($segment), static::$reservedWords, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Prepare anything needed before carving the class, returning false to abort.
     *
     * @param  string  $name
     * @return bool
     */
    protected function prepareCarving($name)
    {
        return true;
    }

    /**
     * Get the fully qualified classes imported by the stub.
     *
     * @return array
     */
    protected function importedClasses()
    {
        return [];
    }

    /**
     * Get the config key of the carved class.
     *
     * @return string
     */
    protected function configKey()
    {
        return Str::snake($this->type);
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        return strtr($this->files->get($this->getStub()), $this->stubReplacements($name));
    }

    /**
     * Get the replacements of the placeholders in the stub.
     *
     * @param  string  $name
     * @return array
     */
    protected function stubReplacements($name)
    {
        return [
            'DummyNamespace' => $this->getNamespace($name),
            'Dummy'.$this->type => class_basename($name),
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite the '.$this->type.' if it already exists'],
        ];
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $rootNamespace = $this->rootNamespace();
        $name = substr($name, strlen($rootNamespace));
        return $this->rootNamespacePath($rootNamespace).str_replace('\\', '/', $name).'.php';
    }

    /**
     * Get the directory of the root namespace.
     *
     * The directory is resolved by the longest matching namespace prefix in the PSR-4 autoload
     * map of the application's composer.json (e.g. "App\\" => "app/"), so that the carved class
     * can be autoloaded on case-sensitive filesystems. If no prefix matches, the root namespace
     * itself is used as the directory under the base path.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function rootNamespacePath($rootNamespace)
    {
        $namespace = $rootNamespace.'\\';
        $prefix = null;
        $directory = null;

        foreach ($this->autoloadPsr4() as $psr4Prefix => $paths) {
            $psr4Prefix = trim($psr4Prefix, '\\').'\\';
            $paths = (array) $paths;
            if (Str::startsWith($namespace, $psr4Prefix) && strlen($psr4Prefix) > strlen((string) $prefix) && count($paths) > 0) {
                $prefix = $psr4Prefix;
                $directory = reset($paths);
            }
        }

        if (is_null($prefix)) {
            return base_path(str_replace('\\', '/', $rootNamespace));
        }

        $subdirectory = str_replace('\\', '/', trim(substr($namespace, strlen($prefix)), '\\'));
        return rtrim(base_path(trim($directory, '/').(blank($subdirectory) ? '' : '/'.$subdirectory)), '/');
    }

    /**
     * Get the PSR-4 autoload map of the application's composer.json.
     *
     * @return array
     */
    protected function autoloadPsr4()
    {
        $composer = base_path('composer.json');
        if (! is_file($composer)) {
            return [];
        }
        $psr4 = data_get(json_decode(file_get_contents($composer), true), 'autoload.psr-4', []);
        return is_array($psr4) ? $psr4 : [];
    }
}
