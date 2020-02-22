<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Revision;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BlogArticleController extends Controller {
    public function get_article(Request $request, $id){
        $article = Article::find($id);
       return response()->json(new ArticleResource($article), 200);
    }

    public function get_article_list(Request $request){
        return response()->json(ArticleResource::collection(Article::all()), 200);
    }

    public function update_article(Request $request, $id){
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
        return response($article, 200);
    }
}
