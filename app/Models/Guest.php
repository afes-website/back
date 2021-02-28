<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'entered_at', 'exited_at', 'exh_id', 'term_id', 'reservation_id'
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

    public $timestamps = true;

    const CREATED_AT = 'entered_at';

    const UPDATED_AT = null;

    public function reservation() {
        return $this->belongsTo('\App\Models\Reservation');
    }

    public function logs() {
        return $this->hasMany('\App\Models\ActivityLog');
    }

    public function term() {
        return $this->belongsTo('\App\Models\Term');
    }

}
