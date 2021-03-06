<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class RevisionResource extends Resource {

    /**
     * リソースを配列へ変換する
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request) {
        return [
            'id' => $this->id,
            'title' => $this->removeWrap($this->title),
            'article_id' => $this->article_id,
            'timestamp' => $this->timestamp->toIso8601ZuluString(),
            'content' => $this->content,
            'status' => $this->status,
            'author' => $this->user,
            'handle_name' => $this->handle_name
        ];
    }

    private function removeWrap($title) {
        $res = $title;
        $res = rawurldecode($res);
        $res = str_replace("\n", '', $res);
        $res = str_replace("\\n", '', $res);
        return $res;
    }
}
