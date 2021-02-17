<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Reservation extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'people_count', 'name', 'term_id', 'email', 'address', 'cellphone', 'guest_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public function guest() {
        return $this->belongsTo('\App\Models\Guest');
    }

    public function term() {
        return $this->belongsTo('\App\Models\Term');
    }

    public function hasProblem() {
        $term = $this->term;
        $current = Carbon::now();

        if(
            new Carbon($term->enter_scheduled_time) > $current
            || new Carbon($term->exit_scheduled_time) < $current
        ) {
            return 'OUT_OF_RESERVATION_TIME';
        }

        if($this->guest_id !== NULL){
            return 'ALREADY_ENTERED_RESERVATION';
        }

        return false;
    }
}
