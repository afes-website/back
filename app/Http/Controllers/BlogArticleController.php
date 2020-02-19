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
        return new ArticleResource($article);
    }
}
