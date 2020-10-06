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
        $exh = Exhibition::find($id);
        if(!$exh->exists()){
            abort(404);
        }
        $q = $this->validate($request, [
            'name' => ['string'],
            'type' => ['string', 'regex:/^(normal|frontier|stage)$/'],
            'thumbnail_image_id' => ['string']
        ]);

        $exh->update($q);

        return response(new ExhibitionResource($exh),201);
    }

    public function create(Request $request){
        $q = $this->validate($request, [
            'id' => ['string', 'required'],
            'name' => ['string', 'required'],
            'type' => ['string', 'required', 'regex:/^(normal|frontier|stage)$/'],
            'thumbnail_image_id' => ['string', 'required']
        ]);

        if(Exhibition::find($q['id'])->exists()){
            abort(400);
        };

        $exhibition = Exhibition::create($q);

        return response(new ExhibitionResource($exhibition),201);
    }
}
