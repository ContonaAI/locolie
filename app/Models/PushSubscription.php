<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['endpoint', 'public_key', 'auth_token', 'category_prefs'])]
class PushSubscription extends Model
{
    protected function casts(): array
    {
        return ['category_prefs' => 'array'];
    }
}
