<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use Illuminate\Http\Request;

class BlogRevisionController extends Controller {
    public function index(){
        return response()->json(Revision::all());
    }

    public function create(Request $request) {
        $validated_request = $this->validate($request, [
            'title' => ['required'],
            'article_id'=> ['string'],
            'user_id'=> ['string'],
            'content' => ['required', 'string'],
        ]);
        return response()->json(Revision::create($validated_request), 201);
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
