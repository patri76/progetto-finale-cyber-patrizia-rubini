<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /*
    |--------------------------------------------------------------------------
    | Challenge 6 - Mass Assignment / Fillable Vulnerability
    |--------------------------------------------------------------------------
    |
    | Vulnerabilità:
    | Nel modello User erano presenti tra i campi fillable anche:
    | - is_admin
    | - is_revisor
    | - is_writer
    |
    | Questo rappresentava una vulnerabilità di Mass Assignment,
    | perché un utente malevolo avrebbe potuto manipolare una request
    | HTTP aggiungendo campi non previsti dal form, ad esempio:
    |
    | is_admin=1
    |
    | ottenendo privilegi amministrativi senza autorizzazione.
    |
    | Mitigazione:
    | Sono stati rimossi da $fillable tutti i campi relativi ai ruoli.
    |
    | Il modello ora consente il mass assignment solo per:
    | - name
    | - email
    | - password
    |
    | Dopo la mitigazione eventuali campi come:
    | is_admin
    | is_revisor
    | is_writer
    |
    | inviati dall’utente vengono ignorati dal framework.
    |
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
