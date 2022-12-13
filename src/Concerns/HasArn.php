<?php

namespace RenokiCo\LaravelAcl\Concerns;

use RenokiCo\Acl\Concerns\HasArn as HasAclArn;
use RenokiCo\LaravelAcl\LaravelAcl;

trait HasArn
{
    use HasAclArn;

    /**
     * The unique ID of this resource.
     *
     * @return string|int
     */
    public function arnResourceId()
    {
        return $this->getKey();
    }

    /**
     * Get the registered metadata (like actions) for this ARNable.
     *
     * @return null|\RenokiCo\LaravelAc\RegisteredArnable
     */
    public static function getRegisteredArnable()
    {
        return LaravelAcl::getRegisteredArnable(static::class);
    }

    /**
     * Resolve the registry of a list of actions that are used for agnostic ARNs
     * (ARNs that are not tied to a specific resource ID), like List or Create.
     *
     * @return array<int, string>
     *
     * @see https://github.com/renoki-co/acl#resource-agnostic-arn-vs-resource-arn
     */
    public static function arnableAgnosticActionsToRegister(): array
    {
        return [
            'List',
            'Create',
        ];
    }

    /**
     * Resolve the registry of a list of actions that are used for specific ARNs
     * (ARNs that are tied to a specific resource ID), like Update or Delete.
     *
     * @return array<int, string>
     *
     * @see https://github.com/renoki-co/acl#resource-agnostic-arn-vs-resource-arn
     */
    public static function arnableActionsToRegister(): array
    {
        return [
            'Describe',
            'Update',
            'Delete',
        ];
    }
}
