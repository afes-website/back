<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExhibitionResource;
use App\Models\Exhibition;
use Illuminate\Http\Request;


class ExhibitionController extends Controller {
    public function index(Request $request){
        return response(ExhibitionResource::collection(Exhibition::all()));
    }
}
