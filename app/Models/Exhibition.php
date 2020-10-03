<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exhibition extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'thumbnail_image_id', 'draft_id', 'updated_at'
    ];

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = true;

    const UPDATED_AT = 'updated_at';

    const CREATED_AT = null;

    public function draft() {
        return $this->belongsTo('\App\Models\Draft');
    }

}
