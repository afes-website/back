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
            'updated_at' => ['string'],
            'q' => ['string'],
        ]);
        $response = Article::query();

        foreach ($query as $i => $value){
            if ($i === 'q')continue;
            $response->where($i, $value);
        }

        if (!$request->has('q'))
            return response()->json(ArticleResource::collection($response->get()));

        $ids = $response->get('id');
        $q = explode(' ', $request->input('q'));
        $articles = Article::whereIn('id', $ids)->get();
        $revision_query = Revision::whereIn('id', $articles->pluck('revision_id'));
        foreach ($q as $query) {
            $revision_query->where(function ($t) use ($query) {
                $t->where('title', 'LIKE', '%'.$query.'%')
                    ->orWhere('content', 'LIKE', '%'.$query.'%');
            });
        }
        $matched_article_ids = $revision_query->get()->pluck('article_id');

        $matched_articles = $articles->whereIn('id', $matched_article_ids);
        return response()->json(ArticleResource::collection($matched_articles));
    }

    public function show($id){
        $article = Article::find($id);
        if(!$article) abort(404);

        return response()->json(new ArticleResource($article));
    }

    public function update(Request $request, $id){
        $this->validate($request, [
            'category' => ['required', 'string'],
            'revision_id' => ['required', 'int']
        ]);
        $rev = Revision::find($request->input('revision_id'));

        if(!$rev) abort(404);
        if($rev->status != 'accepted') abort(408, "Revision isn't accepted");
        if($rev->article_id != $id) abort(400, "The specified revision's article_id is different");

        $article = Article::updateOrCreate(['id' => $id],[
            'title' => $rev->title,
            'category' => $request->input('category'),
            'revision_id' => $rev->id
        ]);
        return response(new ArticleResource($article));
    }

    public function destroy($id){
        $article = Article::find($id);
        if(!$article) abort(404);

        $article->delete($id);

        return response("{}", 204);
    }
}
