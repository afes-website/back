<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DraftCommentResource extends JsonResource {

    /**
     * リソースを配列へ変換する
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request) {
        return [
            'id' => $this->id,
            'author' => new UserResource($this->author),
            'content' => $this->content,
            'created_at' => $this->created_at
        ];
    }
}
