<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Adldap\Laravel\Facades\Adldap;
use Illuminate\Support\Facades\Input;

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
    
    public function roles()
    {
        return $this
            ->belongsToMany('App\Role')
            ->withTimestamps();
    }
    
    public function groups() {
        $provider = Adldap::getProvider(config('ldap_auth.connection'));
        $user = $provider->search()->users()->where('mail', '=', $this->email)->first();

        if (!$user) {
            return array();
        }
        
        $recursive = boolval(Input::get('recursive'));
        
        if ($filter = Input::get('filter')) {
            $groups = $user->getGroups(['cn'], $recursive);//->where('cn', 'contains', $filter);
//            $groups = $provider->search()->where([
//                [$this->schema->objectClass() => $this->schema->objectClassGroup()],
//                ['cn', 'contains', $filter],
//                ['member', '=', $user->cn]
//            ])->get();
                
            $names = [];

            foreach ($groups as $group) {
                $names[] = $group->getCommonName();
            }
            
        } else {
            $names = $user->getGroupNames($recursive);
        }
        
        return $names;
    }
    
    public function inGroup() {
        $provider = Adldap::getProvider(config('ldap_auth.connection'));
        $user = $provider->search()->users()->where('mail', '=', $this->email)->first();
        
        $group = Input::get('group');
        
        if ($user->inGroup($group)) {
            $group = $provider->search()->groups()->where('cn', '=', $group)->first();
            $attrs = $group->getAttribute('cn');
            return $attrs;
        }
        
        return array();
    }
    
    public function findForPassport($username) {
        return $this->where('username', $username)->first();
    }
    
    public function validateForPassportPasswordGrant($password) {
        return Auth::validate([
            'username' => $this->getAttribute("username"),
            'password' => $password
        ]);
    }
    
    public function authorizeRoles($roles)
    {
        if ($this->hasAnyRole($roles)) {
            return true;
        }
        abort(401, __('unauthorized'));
    }
    
    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
        } else {
            if ($this->hasRole($roles)) {
                return true;
            }
        }
        return false;
    }
    
    public function hasRole($role)
    {
        if ($this->roles()->where('name', $role)->first()) {
            return true;
        }
        return false;
    }
}
