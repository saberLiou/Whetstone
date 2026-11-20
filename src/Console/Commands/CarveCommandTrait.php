<?php

namespace saberLiou\Whetstone\Console\Commands;

use Illuminate\Support\Str;

trait CarveCommandTrait
{
    /**
     * Avoid the conflict of user-defined class name has the same prefix with the root namespace in the config.
     *
     * @param string $name
     * @param string $class
     * @return string $name
     */
    public function refineConflictNameForNamespace($name, $class)
    {
        $namespace = config('whetstone.roots.'.$class).'\\'.config('whetstone.namespaces.'.$class).'\\';
        if (substr($name, 0, strlen($namespace)) !== $namespace) {
            $name = $namespace.$name;
        }
        return $name;
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $rootNamespace = trim($this->rootNamespace(), '\\');
        $name = Str::replaceFirst($rootNamespace, '', $name);
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
