<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'exh_id', 'content', 'teacher_review_status', 'review_status', 'published', 'created_at'
    ];

    protected $attributes = [
        'review_status' => 'waiting',
        'teacher_review_status' => 'waiting',
        'published' => false
    ];

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = true;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = null;

    protected $appends = ['status', 'deleted'];

    public function exhibition() {
        return $this->belongsTo('\App\Models\Exhibition', 'exh_id');
    }

    public function comments() {
        return $this->hasMany('\App\Models\Comments');
    }

    public function getStatusAttribute() {
        if($this->teacher_review_status === 'accepted' && $this->review_status === 'accepted') {
            return 'accepted';
        }

        if($this->teacher_review_status === 'rejected' || $this->review_status === 'rejected') {
            return 'rejected';
        }
        return 'waiting';
    }

    public function scopeStatus($query, $status) {
        if($status == 'accepted') {
            return $query->where('teacher_review_status', 'accepted')->where('review_status', 'accepted');
        }
        if($status == 'rejected') {
            return $query->where('teacher_review_status', 'rejected')->orWhere('review_status', 'rejected');
        }
        if($status == 'waiting') {
            return $query
                ->where(function ($query) {
                    $query
                        ->where('teacher_review_status', '!=', 'accepted')
                        ->orWhere('review_status', '!=', 'accepted');
                }) // not accepted
                ->Where(function ($query) {
                    $query
                        ->where('teacher_review_status', '!=', 'rejected')
                        ->Where('review_status', '!=', 'rejected');
                }); // not rejected
        }
        return $query->where('teacher_review_status', $status)->where('review_status', $status);
    }

    public function getDeletedAttribute() {
        if($this->published === false) return false;
        if($this->exhibition->draft_id == $this->id) return false;
        return true;
    }
}
