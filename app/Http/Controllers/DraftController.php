<?php

namespace App\Http\Controllers;

use App\Http\Resources\DraftResource;
use App\Models\Draft;
use App\Models\DraftComments;
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

        SlackNotify::notify_draft($draft, 'created', $user->name);

        return response(new DraftResource($draft),201);
    }

    public function publish(Request $request, $id) {
        $draft = Draft::find($id);
        if(!$draft)
            abort(404);

        if($draft->status != 'accepted')
            abort(400);

        $draft->update(['published' => true]);
        $draft->exhibition->update(['draft_id' => $id]);

        SlackNotify::notify_draft($draft, 'published', $request->user()->name);

        return response()->json(new DraftResource($draft));
    }

    public function accept(Request $request, $id) {
        $draft = Draft::find($id);
        if(!$draft)
            abort(404);

        if($request->user()->has_permission('blogAdmin')) {
            $draft->update(['review_status' => 'accepted']);
            SlackNotify::notify_draft($draft, 'accepted(admin)', $request->user()->name);
        }
        if($request->user()->has_permission('teacher')) {
            $draft->update(['teacher_review_status' => 'accepted']);
            SlackNotify::notify_draft($draft, 'accepted(teacher)', $request->user()->name);
        }

        return response()->json(new DraftResource($draft));
    }

    public function reject(Request $request, $id) {
        $draft = Draft::find($id);
        if(!$draft)
            abort(404);

        if($request->user()->has_permission('blogAdmin')) {
            $draft->update(['review_status' => 'rejected']);
            SlackNotify::notify_draft($draft, 'rejected(admin)', $request->user()->name);
        }
        if($request->user()->has_permission('teacher')) {
            $draft->update(['teacher_review_status' => 'rejected']);
            SlackNotify::notify_draft($draft, 'rejected(teacher)', $request->user()->name);
        }

        return response()->json(new DraftResource($draft));
    }

    public function comment(Request $request, $id) {
        $draft = Draft::find($id);
        if(!$draft)
            abort(404);

        $this->validate($request, [
            'comment' => ['string', 'required']
        ]);

        DraftComments::create([
            'draft_id' => $id,
            'author_id' => $request->user()->id,
            'content' => $request->input('comment')
        ]);

        return response()->json(new DraftResource(Draft::find($id)));
    }
}
