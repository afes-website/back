<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ExhibitionRoomResource extends Resource {

    /**
     * リソースを配列へ変換する
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request) {
        return [
            'count' => $this->countGuest(),
            'limit' => $this->capacity,
            'room_id' => $this->room_id,
        ];
    }
}
