<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Revision;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BlogController extends Controller {

    public function get_article(Request $request, $id){
        $article = Article::find($id);
        return new ArticleResource($article);
    }

    public function get_revision($id){
        $revision = Revision::find($id);

        if(!$revision)
            throw new HttpException(404);

        $revision -> timestamp = date(DATE_ISO8601, $revision -> timestamp);
        $article_info = $revision;
        return response()->json($article_info, 200);
    }
}
