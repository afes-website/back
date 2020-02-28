<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ArticleResource extends Resource
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
            'category' => $this->category,
            'title' => $this->title,
            'author' => $this->revision->user,
            'revision_id' => $this->revision_id,
            'created_at' => $this->created_at->toIso8601ZuluString(),
            'updated_at' => $this->updated_at->toIso8601ZuluString(),
            'content' => $this->revision->content,
        ];
    }
}
