<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name', // Este será nuestro 'usuario'
        'cedula',
        'first_name',
        'second_name',
        'first_surname',
        'second_surname',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Accessor para obtener el nombre completo automáticamente.
     */
    public function getFullNameAttribute(): string
    {
        return trim(sprintf('%s %s %s %s',
            $this->first_name,
            $this->second_name,
            $this->first_surname,
            $this->second_surname
        ));
    }
}