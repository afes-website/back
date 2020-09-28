<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ExhibitionResource extends Resource
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
            'name' => $this->name,
            'thumbnail_image_id' => $this->thumbnail_image_id,
            'content' => $this->draft->content,
            'updated_at' => $this->updated_at
        ];
    }
}

