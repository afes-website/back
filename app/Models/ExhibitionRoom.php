<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExhibitionRoom extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'id', 'room_id', 'capacity', 'guest_count', 'updated_at'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $table = 'exh_rooms';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $timestamps = true;

    const CREATED_AT = null;

    const UPDATED_AT = 'updated_at';

    public function guests() {
        return $this->hasMany('\App\Models\Guest', 'exh_id');
    }

    public function countGuest() {
        $terms = Term::all();
        $res = [];
        foreach($terms as $term){
            $guest = $this->guests->where('term_id',$term->id);
            $count = count($guest);
            if($count==0) continue;
            $res[$term->id] = $count;
        }
        return $res;
    }
}
