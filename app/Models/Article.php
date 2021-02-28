<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model {


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'category', 'title', 'revision_id', 'created_at', 'updated_at', 'handle_name',
    ];

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = true;

    public function revision() {
        return $this->belongsTo('\App\Models\Revision');
    }
}
