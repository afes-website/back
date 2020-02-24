<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use Illuminate\Http\Request;

class BlogRevisionController extends Controller {
    public function index(Request $request){
        if($request->user('admin'))
            return response()->json(Revision::all());

        if($request->user('writer'))
            return response()->json(
                Revision::where('user_id', $request->user('writer')->id)->get()
            );

        abort(403);
    }

    public function create(Request $request) {
        $this->validate($request, [
            'title' => ['required', 'string'],
            'article_id'=> ['required', 'string'],
            'content' => ['required', 'string'],
        ]);

        return response()->json(Revision::create([
            'title' => $request->input('title'),
            'article_id' => $request->input('article_id'),
            'user_id' => $request->user('writer')->id,
            'content' => $request->input('content')
        ]), 201);
    }

    public function show($id){
        $revision = Revision::find($id);

        if(!$revision)
            abort(404);

        $article_info = $revision;
        return response()->json($article_info);
    }

    public function accept($id){
        $revision = Revision::find($id);
        if(!$revision)
            abort(404);

        $revision->update(['status' => 'accepted']);

        return response()->json($revision);
    }

    public function reject($id){
        $revision = Revision::find($id);
        if(!$revision)
            abort(404);

        $revision->update(['status' => 'rejected']);

        return response()->json($revision);
    }

}
