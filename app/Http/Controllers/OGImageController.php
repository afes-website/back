<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
class OGImageController extends Controller {
    private function generate($title, $author = NULL, $category = NULL){
        return;
    }

    public function getImage(Request $request){
        $this->validate($request, [
            'title' => ['required', 'string'],
        ]);
    }

    public function getArticleImage($id){
        $article = Article::find($id);
        if(!$article) abort(404);
    }

    public function getPreview(Request $request){
        $this->validate($request, [
            'title' => ['required', 'string'],
            'author'=> ['string'],
            'category' => ['string'],
        ]);
    }
}
