<?php

namespace RenokiCo\LaravelAcl\Test\Models;

use Illuminate\Database\Eloquent\Model;
use RenokiCo\LaravelAcl\Concerns\HasArn;
use RenokiCo\LaravelAcl\Contracts\Arnable;

class Vps extends Model implements Arnable
{
    use HasArn {
        arnableAgnosticActionsToRegister as originalAgnosticActions;
        arnableActionsToRegister as originalArnableActions;
    }

    protected $fillable = [
        'id',
        'user_id',
        'name',
    ];

    public function arnResourceAccountId()
    {
        return $this->user_id;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function arnableAgnosticActionsToRegister(): array
    {
        return array_merge(static::originalAgnosticActions(), [
            'CheckAvailability',
        ]);
    }

    public static function arnableActionsToRegister(): array
    {
        return array_merge(static::originalArnableActions(), [
            'Reboot',
        ]);
    }
}
