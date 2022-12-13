<?php

namespace RenokiCo\LaravelAcl\Contracts;

use RenokiCo\Acl\Contracts\Arnable as AclArnable;

interface Arnable extends AclArnable
{
    /**
     * Resolve the registry of a list of actions that are used for agnostic ARNs
     * (ARNs that are not tied to a specific resource ID), like List or Create.
     *
     * @return array<string>
     * @see https://github.com/renoki-co/acl#resource-agnostic-arn-vs-resource-arn
     */
    public static function arnableAgnosticActionsToRegister(): array;

    /**
     * Resolve the registry of a list of actions that are used for specific ARNs
     * (ARNs that are tied to a specific resource ID), like Update or Delete.
     *
     * @return array<string>
     * @see https://github.com/renoki-co/acl#resource-agnostic-arn-vs-resource-arn
     */
    public static function arnableActionsToRegister(): array;
}
