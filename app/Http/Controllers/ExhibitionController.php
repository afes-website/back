<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExhibitionResource;
use App\Models\Exhibition;
use Illuminate\Http\Request;


class ExhibitionController extends Controller {
    public function index(Request $request){
        // TODO: Query
        return response(ExhibitionResource::collection(Exhibition::all()));
    }

    public function show(Request $request, $id){
        $exh = Exhibition::find($id);
        if(!$exh)
            abort(404);
        return response(new ExhibitionResource($exh));
    }

    public function patch(Request $request, $id){
        // TODO: patch
    }

    public function create(Request $request){
        // TODO: create
    }
}
