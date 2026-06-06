<?php

namespace A2ZWeb\EmailSequences\Tests;

use A2ZWeb\EmailSequences\Concerns\HasEmailSequences;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasEmailSequences;
    use HasFactory;
    use Notifiable;

    protected $guarded = [];

    protected $hidden = ['password'];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
