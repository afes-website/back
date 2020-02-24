<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Revision;
use Illuminate\Http\Request;

class BlogArticleController extends Controller {
    public function index(Request $request){
        $query = $this->validate($request, [
            'id' => ['string'],
            'category' => ['string'],
            'revision_id' => ['int'],
            'created_at' => ['string'],
            'updated_at' => ['string']
        ]);
        $response = Article::query();

        foreach ($query as $i => $value){
            $response->where($i, $value);
        }

        return response()->json(ArticleResource::collection($response->get()));
    }

    public function show($id){
        $article = Article::find($id);
        return response()->json(new ArticleResource($article));
    }

    public function update(Request $request, $id){
        $this->validate($request, [
            'category' => ['required', 'string'],
            'revision_id' => ['required', 'int']
        ]);
        $rev = Revision::find($request->input('revision_id'));

        if(!$rev) abort(404);
        if($rev->status != 'accepted') abort(400, "Revision isn't accepted");
        if(!$id) abort(408);

        $article = Article::updateOrCreate(['id' => $id],[
            'title' => $rev->title,
            'category' => $request->input('category'),
            'revision_id' => $rev->id
        ]);
        return response($article);
    }

    public function destroy($id){
        $article = Article::find($id);
        if(!$article) abort(404);

        $article->delete($id);

        return response("{}", 204);
    }
}
