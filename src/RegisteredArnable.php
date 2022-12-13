<?php

namespace RenokiCo\LaravelAcl;

class RegisteredArnable
{
    /**
     * Initialize a new Registered ARNables with metadata.
     *
     * @param  string  $name
     * @param  array  $actions
     * @param  array  $agnosticActions
     * @param void
     */
    public function __construct(
        public string $name,
        public array $actions = [],
        public array $agnosticActions = [],
    ) {
        //
    }
}
