<?php

namespace App\Http\Controllers;

use App\Http\Resources\RevisionResource;
use App\Models\Revision;
use Illuminate\Http\Request;
use App\SlackNotify;

class BlogRevisionController extends Controller {
    public function index(Request $request){
        $response = Revision::query();
        if(!$request->user('admin') && !$request->user('writer'))
            abort(401);

        if(!$request->user('admin'))
            $response->where('user_id', $request->user('writer')->id);

        $query = $this->validate($request, [
            'id' => ['int'],
            'title' => ['string'],
            'article_id' => ['string'],
            'author_id' => ['string'],
            'timestamp' => ['string'],
            'content' => ['string'],
            'status' => ['string']
        ]);

        foreach ($query as $i => $value){
            if($i == 'author_id'){
                $response->where('user_id', $value);
            }
            else {
                $response->where($i, $value);
            }
        }

        return response(RevisionResource::collection($response->get()));
    }

    public function create(Request $request) {
        $this->validate($request, [
            'title' => ['required', 'string'],
            'article_id'=> ['required', 'string'],
            'content' => ['required', 'string'],
        ]);

        $revision = Revision::create(
            [
                'title' => $request->input('title'),
                'article_id' => $request->input('article_id'),
                'user_id' => $request->user('writer')->id,
                'content' => $request->input('content')
            ]);

        SlackNotify::send([
            "text" => "{$request->user('writer')->name} has created new revision {$revision->id}",
            "attachments" => [
                [
                    "text"=>
                        "title: {$revision->title}\n".
                        "<".env('FRONT_URL')."/blog/admin/paths/{$request->input('article_id')}|manage>"
                ],
            ]
        ]);

        return response(new RevisionResource($revision),201);
    }

    public function show(Request $request, $id){
        $revision = Revision::find($id);
        if($request->user('admin')) {
            if(!$revision)  abort(404);
        }else{
            if(!$request->user('writer')) abort(401);
            if(!$revision)  abort(404);

            if($request->user('writer')->id != $revision->user_id)
                abort(403);
        }

        return response()->json(new RevisionResource($revision));
    }

    public function accept(Request $request, $id){
        $revision = Revision::find($id);
        if(!$revision)
            abort(404);

        $revision->update(['status' => 'accepted']);

        SlackNotify::send([
            "text" => "{$request->user('admin')->name} has accepted revision {$id}.",
            "attachments" => [
                [
                    "text"=>
                        "title: {$revision->title}\n".
                        "<".env('FRONT_URL')."/blog/admin/paths/{$revision->article_id}|manage>"
                ],
            ]
        ]);

        return response()->json(new RevisionResource($revision));
    }

    public function reject(Request $request, $id){
        $revision = Revision::find($id);
        if(!$revision)
            abort(404);

        $revision->update(['status' => 'rejected']);

        SlackNotify::send([
            "text" => "{$request->user('admin')->name} has rejected revision {$id}.",
            "attachments" => [
                [
                    "text"=>
                        "title: {$revision->title}\n".
                        "<".env('FRONT_URL')."/blog/admin/paths/{$revision->article_id}|manage>"
                ],
            ]
        ]);

        return response()->json(new RevisionResource($revision));
    }

}
