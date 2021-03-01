<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExhibitionResource;
use App\Models\Exhibition;
use App\SlackNotify;
use Illuminate\Http\Request;

class ExhibitionController extends Controller {
    public function index(Request $request) {
        $query = $this->validate($request, [
            'id' => ['string'],
            'name' => ['string'],
            'type' => ['string'],
            'room_id' => ['string'],
        ]);

        $exhibitions = Exhibition::query();

        foreach ($query as $i => $value) {
            $exhibitions->where($i, $value);
        }

        return response(ExhibitionResource::collection($exhibitions->get()));
    }

    public function show(Request $request, $id) {
        $exhibition = Exhibition::find($id);
        if (!$exhibition)
            abort(404);
        return response(new ExhibitionResource($exhibition));
    }

    public function patch(Request $request, $id) {
        $exhibition = Exhibition::find($id);
        if ($exhibition === null) {
            abort(404);
        }
        $q = $this->validate($request, [
            'name' => ['string'],
            'type' => ['in:normal,frontier,stage'],
            'thumbnail_image_id' => ['string']
        ]);

        $exhibition->update($q);

        SlackNotify::notifyExhibition($exhibition, 'patched', $request->user()->name);


        return response(new ExhibitionResource($exhibition), 201);
    }

    public function create(Request $request) {
        $q = $this->validate($request, [
            'id' => ['string', 'required'],
            'name' => ['string', 'required'],
            'type' => ['required', 'in:normal,frontier,stage'],
            'thumbnail_image_id' => ['string', 'required']
        ]);

        if (Exhibition::find($q['id']) !== null) {
            abort(400);
        };

        $exhibition = Exhibition::create($q);

        SlackNotify::notifyExhibition($exhibition, 'created', $request->user()->name);

        return response(new ExhibitionResource($exhibition), 201);
    }
}
