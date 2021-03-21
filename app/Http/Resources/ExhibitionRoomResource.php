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
            'info' => [
                'name' => $this->name,
                'room_id' => $this->room_id,
                'thumbnail_image_id' => $this->thumbnail_image_id,
            ],
            'count' => $this->countGuest(),
            'limit' => $this->capacity,
        ];
    }
}
