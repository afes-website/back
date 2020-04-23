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

        $this->generate($request->input('title'));
    }

    public function getArticleImage($id){
        $article = Article::find($id);
        if(!$article) abort(404);

        $this->generate($article->title, $article->revision->user->name, $article->category);
    }

    public function getPreview(Request $request){
        $this->validate($request, [
            'title' => ['required', 'string'],
            'author'=> ['string'],
            'category' => ['string'],
        ]);
        $this->generate($request->input('title'), $request->input('author'), $request->input('category'));
    }
}
