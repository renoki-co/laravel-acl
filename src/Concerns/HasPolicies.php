<?php

namespace RenokiCo\LaravelAcl\Concerns;

use RenokiCo\Acl\Concerns\HasPolicies as HasAclPolicies;
use RenokiCo\Acl\Contracts\Arnable;
use RenokiCo\Acl\Policy;

/**
 * @property \Illuminate\Database\Eloquent\Collection $policies
 */
trait HasPolicies
{
    use HasAclPolicies {
        isAllowedTo as originalIsAllowedTo;
    }

    /**
     * The relationships of the current model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function policies()
    {
        /** @var \Illuminate\Database\Eloquent\Model $this */
        return $this->morphMany(
            config('acl.models.policy'),
            'subject',
        );
    }

    /**
     * Check if this actor is able to perform a specific action.
     *
     * @param  string  $action
     * @param  string|Arnable  $arn
     * @return bool
     *
     * @throws \RenokiCo\Acl\Exceptions\WildcardNotPermittedException
     */
    public function isAllowedTo(string $action, string|Arnable $arn): bool
    {
        $this->loadPolicies(
            $this->policies
                ->map(fn ($aclPolicy) => $aclPolicy->toPolicyInstance())
                ->all(),
        );

        return $this->originalIsAllowedTo($action, $arn);
    }

    /**
     * Attach a policy to the model.
     *
     * @param  string $name
     * @param  \RenokiCo\Acl\Policy  $policy
     * @return \RenokiCo\LaravelAcl\Models\AclPolicy
     */
    public function attachPolicy(string $name, Policy $policy)
    {
        return $this->policies()->create([
            'name' => $name,
            'policy' => $policy->toArray(),
        ]);
    }

    /**
     * Resolve the account ID of the current actor.
     * This value will be used in ARNs for ARNable static instances,
     * to see if the current actor can perform ID-agnostic resource actions.
     *
     * @return null|string|int
     */
    public function resolveArnAccountId()
    {
        /** @var \Illuminate\Database\Eloquent\Model $this */
        return $this->getKey();
    }

    /**
     * Resolve the region of the current actor.
     * This value will be used in ARNs for ARNable static instances,
     * to see if the current actor can perform ID-agnostic resource actions.
     *
     * @return null|string|int
     */
    public function resolveArnRegion()
    {
        return 'local';
    }
}
