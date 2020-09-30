<?php

namespace App\Http\Controllers;

use App\Http\Resources\DraftResource;
use App\Models\Draft;
use App\Models\Exhibition;
use App\SlackNotify;
use Illuminate\Http\Request;


class DraftController extends Controller {
    public function index(Request $request){
        return response(DraftResource::collection(Draft::all()));
    }

    public function show(Request $request, $id){
        $draft = Draft::find($id);
        if($request->user()->has_permission('blogAdmin')) {
            if(!$draft)  abort(404);
        }else{
            if(!$draft)  abort(404);

            if($request->user()->id != $draft->exh_id)
                abort(403);
        }

        return response()->json(new DraftResource($draft));
    }

    public function create(Request $request) {
        $this->validate($request, [
            'content' => ['string', 'required'],
            'exh_id' => ['string']
        ]);

        $exh_id = $request->input('exh_id');

        if(!$request->user()->has_permission('blogAdmin')) {
            if($request->user()->id != $exh_id)
                abort(403);
        }
        if(!Exhibition::where('id', $exh_id)->exists()){
            abort(400);
        }

        $user = $request->user();

        $draft = Draft::create(
            [
                'exh_id' => $exh_id,
                'user_id' => $user->id,
                'content' => $request->input('content')
            ]);

        // SlackNotify::notify_revision($draft, 'created', $author);
        // TODO: SlackNotifyのDraft用の処理

        return response(new DraftResource($draft),201);
    }
}
