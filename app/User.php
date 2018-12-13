<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Adldap\Laravel\Facades\Adldap;

use Auth;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'email', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token'
    ];
    
    public function findForPassport($username) {
        return $this->where('username', $username)->first();
    }
    
    public function validateForPassportPasswordGrant($password) {
        return Auth::validate([
            'username' => $this->getAttribute("username"),
            'password' => $password
        ]);
    }
}
