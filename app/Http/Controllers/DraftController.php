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

        if($request->user()->has_permission('blogAdmin')) {
            if($request->filled('exh_id'))
                $exh_id = $request->exh_id;
            else
                abort(400);
        }else if($request->user()->has_permission('exhibition')) {
            if(Exhibition::where('id', $request->user()->id)->exists())
                $exh_id = $request->user()->id;
            else
                abort(403);
            // TODO: 議論ぐちゃってるからあとで決める、今は500がでない最低限に実装(現行のdocsの通りじゃないのに注意)
        }else{
            abort(403);
            return 0;
        }

        $this->validate($request, [
            'content' => ['string', 'required'],
            'exh_id' => ['string']
        ]);

        $author_id = $request->user();

        // TODO: documentが更新されて、author_idをdraftに入れる必要があるから処理しておく

        $draft = Draft::create(
            [
                'exh_id' => $exh_id,
                // TODO: exh_id が定義されてない可能性がある警告がでちゃうから治せるならがんばって治す
                'content' => $request->input('content'),
            ]);

        // SlackNotify::notify_revision($draft, 'created', $author);
        // TODO: SlackNotifyのDraft用の処理

        return response(new DraftResource($draft),201);
    }
}
