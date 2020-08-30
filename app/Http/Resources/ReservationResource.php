<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ReservationResource extends Resource
{
    /**
     * リソースを配列へ変換する
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'term_id' => $this->term_id,
            'people_count' => $this->people_count
        ];
    }
}
