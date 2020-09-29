<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class UserResource extends Resource
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
            'permissions' => [
                'admin'       => $this->perm_admin       == 1,
                'blogAdmin'   => $this->perm_blogAdmin   == 1,
                'blogWriter'  => $this->perm_blogWriter  == 1,
                'exhibition'  => $this->perm_exhibition  == 1,
                'general'     => $this->perm_general     == 1,
                'reservation' => $this->perm_reservation == 1,
                'teacher'     => $this->perm_teacher     == 1,
            ],
        ];
    }
}

