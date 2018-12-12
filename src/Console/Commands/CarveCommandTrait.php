<?php

namespace saberLiou\Whetstone\Console\Commands;

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
}