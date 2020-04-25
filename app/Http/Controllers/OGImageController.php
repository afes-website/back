<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class OGImageController extends Controller {
    const FONT_NOTO = '../resources/fonts/NotoSansJP-Bold.otf';
    const FONT_FAS = '../resources/fonts/FontAwesome5 Free-Solid.otf';

    private function generate($title, $author = NULL, $category = NULL){
        $data = imagecreatefrompng('../resources/img/og_image.png');
        $img = Image::make($data);

        $lines = $this->textWrap($title, 1200, self::FONT_NOTO, 70);
        $center = 315;
        $lineHeight = 100;
        $lineY = $center - $lineHeight / 2 * (count($lines) - 1);
        foreach ($lines as $line) {
            $img->text($line, 600, $lineY, function ($font) {
                $font->file(self::FONT_NOTO);
                $font->size(70);
                $font->align('center');
                $font->valign('middle');
                $font->color('#ffffff');
            });
            $lineY += $lineHeight;
        }

        $iconY = 500;
        $textY = 498;
        $pos = 145;

        if ($author) {
            $img->text("\u{F007}", $pos, $iconY, function ($font) {
               $font->file(self::FONT_FAS);
               $font->size(28);
               $font->align('left');
               $font->valign('bottom');
               $font->color('#ffffff');
            });
            $pos += 38;
            $img->text($author, $pos, $textY, function ($font) {
               $font->file(self::FONT_NOTO);
               $font->size(28);
               $font->align('left');
               $font->valign('bottom');
               $font->color('#ffffff');
            });
            $arr = imageftbbox(28, 0, '../resources/fonts/NotoSansJP-Bold.otf', $author);
            $pos += $arr[2] - $arr[0]; // right bottom - left bottom
        }

        if ($category) {
            $img->text("\u{F07B}", $pos, $iconY - 3, function ($font) {
               $font->file(self::FONT_FAS);
               $font->size(28);
               $font->align('left');
               $font->valign('bottom');
               $font->color('#ffffff');
            });
            $pos += 45;
            $img->text($this->getCategory($category), $pos, $textY, function ($font) {
               $font->file(self::FONT_NOTO);
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

    private function textWrap($text, $width, $font, $fontSize) {
        $wrappedText = [];
        $_s = '';
        while ($text) {
            $_a = mb_substr($text, 0, 1);
            $arr = imageftbbox($fontSize, 0, $font, $_s . $_a);
            $_w = $arr[2] - $arr[0];
            if ($_w > $width) {
                $wrappedText[] = $_s;
                $_s = '';
            } else {
                $_s .= $_a;
                $text = mb_substr($text, 1);
            }
            if (strlen($text) == 0) {
                $wrappedText[] = $_s;
                break;
            }
        }
        return $wrappedText;
    }

    private function getCategory($id) {
        $arr = config('blog.categories');
        if (array_key_exists($id, $arr))
            return $arr[$id]['name'];
        return $id;
    }
}
