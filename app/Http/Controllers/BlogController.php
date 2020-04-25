<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class BlogController extends BaseController
{
    public function index(){
        return response("{}");
    }
}
