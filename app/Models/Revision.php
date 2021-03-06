<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model {


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'title', 'article_id', 'user_id', 'timestamp', 'content', 'status', 'handle_name',
    ];

    protected $attributes = [
        'status' => 'waiting'
    ];

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = true;

    const CREATED_AT = 'timestamp';

    const UPDATED_AT = null;

    public function user() {
        return $this->belongsTo('\App\Models\User');
    }
}
