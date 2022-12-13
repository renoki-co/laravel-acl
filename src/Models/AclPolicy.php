<?php

namespace RenokiCo\LaravelAcl\Models;

use Illuminate\Database\Eloquent\Model;
use RenokiCo\Acl\Policy;

/**
 * @property \RenokiCo\LaravelAcl\Contracts\RuledByPolicies $subject
 */
class AclPolicy extends Model
{
    protected $guarded = [];

    protected $casts = [
        'policy' => 'array',
    ];

    /**
     * The subject this policy beongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Turn the current ACL Policy model to a Policy instance
     * that can be used to check for permissions.
     *
     * @return Policy
     */
    public function toPolicyInstance()
    {
        return Policy::fromArray($this->policy);
    }
}
