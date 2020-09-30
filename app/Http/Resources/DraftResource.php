<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class DraftResource extends Resource
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
            'exhibition' => [
                'id' => $this->exhibition->id,
                'name' => $this->exhibition->name
            ],
            'content' => $this->content,
            'review_status' => $this->review_status,
            'teacher_review_status' => $this->teacher_review_status,
            'status' => $this->status,
            'published' => $this->published == 1,
            'deleted' => $this->deleted == 1,
            'comments' => DraftCommentResource::collection($this->comments),
            'created_at' => $this->created_at
        ];
    }
}

