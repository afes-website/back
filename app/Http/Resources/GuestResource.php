<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class GuestResource extends Resource {

    /**
     * リソースを配列へ変換する
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request) {
        return [
            'id' => $this->id,
            'term' => $this->term,
            'entered_at' => $this->entered_at,
            'exited_at' => $this->exited_at,
            'exh_id' =>$this->exh_id,
        ];
    }
}
