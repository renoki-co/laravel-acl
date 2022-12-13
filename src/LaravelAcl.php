<?php

namespace RenokiCo\LaravelAcl;

class LaravelAcl
{
    /**
     * The list of registered arnables
     * with full metadata.
     *
     * @var array<string, RegisteredArnable>
     */
    public static $resources = [];

    /**
     * Create a new resource that have specific
     * and agnostic actions.
     *
     * @param  string  $arnableClass
     * @param  array  $agnosticActions
     * @param  array  $actions
     * @return RegisteredArnable
     */
    public static function registerArnable(
        string $arnableClass,
        array $agnosticActions = [],
        array $actions = [],
    ) {
        if (! class_exists($arnableClass)) {
            return;
        }

        if ($registeredResource = static::$resources[$arnableClass] ?? null) {
            return $registeredResource;
        }

        $registeredArnable = new RegisteredArnable(
            name: $name = $arnableClass::arnResourceType(),
            actions: array_map(fn ($action) => "{$name}:{$action}", $actions),
            agnosticActions: array_map(fn ($action) => "{$name}:{$action}", $agnosticActions),
        );

        static::$resources[$arnableClass] = $registeredArnable;

        return $registeredArnable;
    }

    /**
     * Register the given Arnable classes.
     *
     * @param array<int, string> $arnableClasses
     * @return void
     */
    public static function registerArnables(array $arnableClasses): void
    {
        foreach ($arnableClasses as $arnableClass) {
            static::registerArnable(
                arnableClass: $arnableClass,
                /** @var \RenokiCo\LaravelAcl\Contracts\Arnable $arnableClass */
                agnosticActions: $arnableClass::arnableAgnosticActionsToRegister(),
                actions: $arnableClass::arnableActionsToRegister(),
            );
        }
    }

    /**
     * Get the registered ARNables.
     *
     * @return array<string, RegisteredArnable>
     */
    public static function getRegisteredResources()
    {
        return static::$resources;
    }

    /**
     * Get the registered ARNable, if it exists.
     *
     * @param  string  $arnableClass
     * @return RegisteredArnable|null
     */
    public static function getRegisteredArnable(string $arnableClass)
    {
        return static::$resources[$arnableClass] ?? null;
    }

    /**
     * Reset the ARNables registry.
     *
     * @return void
     */
    public static function resetArnables()
    {
        static::$resources = [];
    }
}
