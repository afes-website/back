<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class BlogController extends BaseController
{
    public function category_index(){
        return response()->json(config('blog.categories'));
    }
}
