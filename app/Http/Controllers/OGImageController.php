<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class OGImageController extends Controller {
    private function generate($title, $author = NULL, $category = NULL){
        $data = imagecreatefrompng('../resources/img/og_image.png');
        $img = Image::make($data);

        $lines = $title; // TODO: divide
        $img->text($lines, 600, 315, function ($font) {
            $font->file('../resources/fonts/NotoSansJP-Bold.otf');
            $font->size(70);
            $font->align('center');
            $font->valign('middle');
            $font->color('#ffffff');
        });

        $iconY = 500;
        $textY = 498;
        $pos = 145;

        if ($author) {
            $img->text('', $pos, $iconY, function ($font) {
               $font->file('../resources/fonts/FontAwesome5 Free-Solid.otf');
               $font->size(28);
               $font->align('left');
               $font->valign('bottom');
               $font->color('#ffffff');
            });
            $pos += 38;
            $img->text($author, $pos, $textY, function ($font) {
               $font->file('../resources/fonts/NotoSansJP-Bold.otf');
               $font->size(28);
               $font->align('left');
               $font->valign('bottom');
               $font->color('#ffffff');
            });
            $arr = imageftbbox(28, 0, '../resources/fonts/NotoSansJP-Bold.otf', $author);
            $pos += $arr[2] - $arr[0]; // right bottom - left bottom
        }

        if ($category) {
            $img->text('', $pos, $iconY - 3, function ($font) {
               $font->file('../resources/fonts/FontAwesome5 Free-Solid.otf');
               $font->size(28);
               $font->align('left');
               $font->valign('bottom');
               $font->color('#ffffff');
            });
            $pos += 45;
            $img->text($category, $pos, $textY, function ($font) {
               $font->file('../resources/fonts/NotoSansJP-Bold.otf');
               $font->size(28);
               $font->align('left');
               $font->valign('bottom');
               $font->color('#ffffff');
            });
        }

        return response($img->encode())->header('Content-Type', 'image/png');
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
