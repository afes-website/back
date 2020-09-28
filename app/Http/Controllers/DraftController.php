<?php

namespace App\Http\Controllers;

use App\Http\Resources\DraftResource;
use App\Models\Draft;
use Illuminate\Http\Request;


class DraftController extends Controller {
    public function index(Request $request){
        return response(DraftResource::collection(Draft::all()));
    }
}
