<?php

use Illuminate\Support\Str;

$factory->define(\RenokiCo\LaravelAcl\Test\Models\Vps::class, function () {
    return [
        'name' => 'Name'.Str::random(5),
    ];
});
