<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Tests\Support\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Rawilk\Webauthn\Tests\Support\database\factories\UserFactory;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $guarded = ['id'];

    protected $hidden = ['password', 'remember_token'];

    protected static function newFactory(): UserFactory
    {
        return new UserFactory;
    }
}
