<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftComments extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'author_id', 'draft_id', 'content', 'created_at'
    ];

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = true;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = null;

    public function draft() {
        return $this->belongsTo('\App\Models\Draft');
    }

    public function author() {
        return $this->belongsTo('\App\Models\User', 'author_id');
    }
}
