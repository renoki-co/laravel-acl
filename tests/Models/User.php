<?php

namespace RenokiCo\LaravelAcl\Test\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use RenokiCo\LaravelAcl\Concerns\HasPolicies;
use RenokiCo\LaravelAcl\Contracts\RuledByPolicies;

class User extends Authenticatable implements RuledByPolicies
{
    use HasPolicies;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function vpses()
    {
        return $this->hasMany(Vps::class);
    }
}
