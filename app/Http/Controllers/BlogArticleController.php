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
}
