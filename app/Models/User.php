<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract {

    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'password',
        'perm_admin',
        'perm_blogAdmin',
        'perm_blogWriter',
        'perm_exhibition',
        'perm_general',
        'perm_reservation',
        'perm_teacher',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    const VALID_PERMISSION_NAMES = [
        "admin",
        "blogAdmin",
        "blogWriter",
        "exhibition",
        "general",
        "reservation",
        "teacher",
    ];

    public function hasPermission($perm_name) {
        if (!in_array($perm_name, self::VALID_PERMISSION_NAMES))
            throw new \Exception('invalid permission name');

        return ($this->{'perm_' . $perm_name} == 1); // weak comparison because of string
    }
}
