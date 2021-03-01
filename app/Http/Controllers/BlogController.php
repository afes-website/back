<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Carbon\Carbon;

class BlogController extends BaseController {

    public function categoryIndex() {
        $dt = Carbon::createFromFormat('Y-m-d H:i T', '2020-11-05 23:00 +0900');
        return response()->json(config('blog.categories'))
            ->header('Last-Modified', $dt->toRfc7231String());
    }
}
