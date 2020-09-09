<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class GuestResource extends Resource
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
            'color_id' => $this->term->color_id,
            'term_id' => $this->term->id,
            'entered_at' => $this->entered_at,
            'exit_scheduled_time' => $this->term->exit_scheduled_time,
            'exited_at' => $this->exited_at,
            'exh_id' =>$this->exh_id,
        ];
    }

    private function removeWrap($title) {
        $res = $title;
        $res = rawurldecode($res);
        $res = str_replace("%0A", '', $res);
        $res = str_replace("\\n", '', $res);
        return $res;
    }
}
