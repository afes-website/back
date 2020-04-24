<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class OGImageController extends Controller {
    private function generate($title, $author = NULL, $category = NULL){
        $data = imagecreatefrompng('../resources/img/og_image.png');
        $img = Image::make($data);

        return response($img->encode())->header('Content-Type', 'image/jpeg');
    }

    public function getImage(Request $request){
        $this->validate($request, [
            'title' => ['required', 'string'],
        ]);

        return $this->generate($request->input('title'));
    }

    public function getArticleImage($id){
        $article = Article::find($id);
        if (!$article) abort(404);

        return $this->generate(
            $article->title,
            $article->revision->user->name,
            $article->category
        );
    }

    public function getPreview(Request $request){
        $this->validate($request, [
            'title' => ['required', 'string'],
            'author'=> ['string'],
            'category' => ['string'],
        ]);

        return $this->generate(
            $request->input('title'),
            $request->input('author'),
            $request->input('category')
        );
    }
}
