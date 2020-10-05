<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExhibitionResource;
use App\Models\Exhibition;
use Illuminate\Http\Request;


class ExhibitionController extends Controller {
    public function index(Request $request){
        $query = $this->validate($request, [
            'id' => ['string'],
            'name' => ['string'],
            'type' => ['string'],
        ]);

        $exhibitions = Exhibition::query();

        foreach ($query as $i => $value){
            $exhibitions->where($i, $value);
        }

        return response(ExhibitionResource::collection($exhibitions->get()));
    }

    public function show(Request $request, $id){
        $exh = Exhibition::find($id);
        if(!$exh)
            abort(404);
        return response(new ExhibitionResource($exh));
    }

    public function patch(Request $request, $id){
        // TODO: patch
//        $exh = Exhibition::find($id);
//        if(!$exh)
//            abort(404);
//        return response(new ExhibitionResource($exh));
    }

    public function create(Request $request){
        // TODO: create
//        $exh = Exhibition::find($id);
//        if(!$exh)
//            abort(404);
//        return response(new ExhibitionResource($exh));
    }
}
