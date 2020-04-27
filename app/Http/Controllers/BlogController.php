<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Carbon\Carbon;

class BlogController extends BaseController
{
    public function category_index(){
        $dt = Carbon::createFromFormat('Y-m-d H:i:s.u', '2020-04-27 13:33:00.000000');
        return response()->json(config('blog.categories'))
            ->header('Last-Modified', $dt->toRfc7231String());

    }
}
