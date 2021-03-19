<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class TermResource extends Resource {

    /**
     * リソースを配列へ変換する
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request) {
        return [
            "enter_scheduled_time" => $this->enter_scheduled_time,
            "exit_scheduled_time" => $this->exit_scheduled_time,
            "guest_type" => $this->guest_type
        ];
    }
}
