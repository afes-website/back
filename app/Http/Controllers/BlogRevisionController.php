<?php

namespace App\Http\Controllers;

use App\Http\Resources\RevisionResource;
use App\Models\Revision;
use Illuminate\Http\Request;

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
            'user_id' => ['string'],
            'timestamp' => ['string'],
            'content' => ['string'],
            'status' => ['string']
        ]);

        foreach ($query as $i => $value){
            $response->where($i, $value);
        }

        return response(RevisionResource::collection($response->get()));
    }

    public function create(Request $request) {
        $this->validate($request, [
            'title' => ['required', 'string'],
            'article_id'=> ['required', 'string'],
            'content' => ['required', 'string'],
        ]);

        return response(
            new RevisionResource(
                Revision::create(
                    [
                        'title' => $request->input('title'),
                        'article_id' => $request->input('article_id'),
                        'user_id' => $request->user('writer')->id,
                        'content' => $request->input('content')
                    ]
                )
            ),
            201
        );
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

    public function accept($id){
        $revision = Revision::find($id);
        if(!$revision)
            abort(404);

        $revision->update(['status' => 'accepted']);

        return response()->json(new RevisionResource($revision));
    }

    public function reject($id){
        $revision = Revision::find($id);
        if(!$revision)
            abort(404);

        $revision->update(['status' => 'rejected']);

        return response()->json(new RevisionResource($revision));
    }

}
