<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'timestamp', 'exh_id', 'log_type', 'guest_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = true;

    public $timestamps = true;

    const CREATED_AT = 'timestamp';

    const UPDATED_AT = null;

    public function guest() {
        return $this->belongsTo('\App\Models\Guest');
    }
}
