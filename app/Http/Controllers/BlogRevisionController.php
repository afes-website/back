<?php

namespace App\Http\Controllers;

use App\Http\Resources\RevisionResource;
use App\Models\Revision;
use App\Models\User;
use Illuminate\Http\Request;
use App\SlackNotify;
use \Illuminate\Support\Str;


class BlogRevisionController extends Controller {
    public function index(Request $request){
        $response = Revision::query();
        if(!$request->user())
            abort(401);

        if(!$request->user()->has_permission('blogAdmin'))
            $response->where('user_id', $request->user()->id);

        $query = $this->validate($request, [
            'id' => ['int'],
            'title' => ['string'],
            'article_id' => ['string'],
            'author_id' => ['string'],
            'timestamp' => ['string'],
            'content' => ['string'],
            'status' => ['string'],
            'handle_name' => ['string']
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
            'article_id'=> ['required', 'string', 'regex:/^[A-Za-z0-9_\-]+$/'],
            'content' => ['required', 'string'],
            'handle_name' => ['string', 'nullable']
        ]);

        $handle_name = $request->input('handle_name');

        if($handle_name === '')$handle_name = NULL;

        $revision = Revision::create(
            [
                'title' => $request->input('title'),
                'article_id' => $request->input('article_id'),
                'user_id' => $request->user()->id,
                'content' => $request->input('content'),
                'handle_name' => $handle_name
            ]);
        if($handle_name !== NULL){
            $author = "{$request->user()} as {$handle_name}";
        }else{
            $author = $request->user();
        }
        SlackNotify::notify_revision($revision, 'created', $author);

        return response(new RevisionResource($revision),201);
    }

    public function show(Request $request, $id){
        $revision = Revision::find($id);
        if($request->user()->has_permission('blogAdmin')) {
            if(!$revision)  abort(404);
        }else{
            if(!$revision)  abort(404);

            if($request->user()->id != $revision->user_id)
                abort(403);
        }

        return response()->json(new RevisionResource($revision));
    }

    public function accept(Request $request, $id){
        $revision = Revision::find($id);
        if(!$revision)
            abort(404);

        $revision->update(['status' => 'accepted']);

        SlackNotify::notify_revision($revision, 'accepted', $request->user()->name);

        return response()->json(new RevisionResource($revision));
    }

    public function reject(Request $request, $id){
        $revision = Revision::find($id);
        if(!$revision)
            abort(404);

        $revision->update(['status' => 'rejected']);

        SlackNotify::notify_revision($revision, 'rejected', $request->user()->name);

        return response()->json(new RevisionResource($revision));
    }

    public function create_contrib(Request $request) {
        $this->validate($request, [
            'title' => ['required', 'string'],
            'content' => ['required', 'string'],
            'handle_name' => ['string', 'nullable']
        ]);

        $handle_name = $request->input('handle_name');

        if($handle_name === '')$handle_name = NULL;

        $user = User::find('anonymous');

        while(true){
            $article_id = 'contrib_'.Str::random(5);
            if(!Revision::where('article_id', $article_id)->exists()) break;
        }

        $revision = Revision::create(
            [
                'title' => $request->input('title'),
                'article_id' => $article_id,
                'user_id' => $user->id,
                'content' => $request->input('content'),
                'handle_name' => $handle_name
            ]);

        if($handle_name !== NULL){
            $author = "{$user->name} as {$handle_name}";
        }else{
            $author = $user->name;
        }

        SlackNotify::notify_revision($revision, 'created(contribution)', $author);

        return response(new RevisionResource($revision),201);
    }

}
