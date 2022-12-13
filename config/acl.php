<?php

return [

    'models' => [

        /*
        |--------------------------------------------------------------------------
        | Models: ACL Policy
        |--------------------------------------------------------------------------
        |
        | Assign the model to use to store and retrieve the ACL policies.
        | Extend this model in case you want custom logic attached to the model,
        | and make sure to specify the new model FQCN here.
        |
        */

        'policy' => \RenokiCo\LaravelAcl\Models\AclPolicy::class,

    ],

];
