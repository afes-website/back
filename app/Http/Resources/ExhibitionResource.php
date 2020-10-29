<?php

namespace App\Http\Resources;

use App\Models\Draft;
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
        if(!Draft::find($this->draft_id)) {
            $content = null;
        }else{
            $content = $this->draft->content;
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'draft_id' => $this->draft_id,
            'thumbnail_image_id' => $this->thumbnail_image_id,
            'content' => $content,
            'updated_at' => $this->updated_at,
            'type' => $this->type,
            'room_id' => $this->room_id,
        ];
    }
}

