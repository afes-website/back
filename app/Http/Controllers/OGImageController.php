<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class OGImageController extends Controller {
    const FONT_NOTO = __DIR__ . '/../../../resources/fonts/NotoSansJP-Bold.otf';
    const FONT_FAS = __DIR__ . '/../../../resources/fonts/FontAwesome5 Free-Solid.otf';
    const IMG_BASE = __DIR__ . '/../../../resources/img/og_image.png';

    private function generate($title, $author = null, $category = null) {
        $data = imagecreatefrompng(self::IMG_BASE);
        $img = Image::make($data);

        $lines[0] = $title;
        $lines = $this->freeWrap($lines);
        $lines = $this->textWrap($lines, 1200, self::FONT_NOTO, 70);
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
            $arr = imageftbbox(28, 0, self::FONT_NOTO, $author);
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

    public function getImage(Request $request) {
        $this->validate($request, [
            'title' => ['required', 'string'],
        ]);

        return $this->generate($request->input('title'));
    }

    public function getArticleImage($id) {
        $article = Article::find($id);
        if (!$article) abort(404);
        $author = $article->handle_name;

        if ($article->handle_name === null) {
            $author = $article->revision->user->name;
        }
        return $this->generate(
            $article->title,
            $author,
            $article->category
        );
    }

    public function getPreview(Request $request) {
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

    private function textWrap(array $lines, $width, $font, $fontSize) {
        $wrappedLines = [];
        foreach ($lines as $text) {
            $_s = '';
            while ($text) {
                $_a = mb_substr($text, 0, 1);
                $arr = imageftbbox($fontSize, 0, $font, $_s . $_a);
                $_w = $arr[2] - $arr[0];
                if ($_w > $width) {
                    $wrappedLines[] = $_s;
                    $_s = '';
                } else {
                    $_s .= $_a;
                    $text = mb_substr($text, 1);
                }
                if (strlen($text) == 0) {
                    $wrappedLines[] = $_s;
                    break;
                }
            }
        }
        return $wrappedLines;
    }

    private function freeWrap(array $lines) {
        $wrappedLines = [];
        foreach ($lines as $line) {
            $line = preg_replace_callback(
                "/\\\\(.)/",
                function ($match) {
                    switch ($match[1]) {
                        case 'n':
                            return "\n";
                            break;
                        case '\\':
                            return '\\';
                            break;
                        default:
                            return '\\' . $match[1];
                            break;
                    }
                },
                $line
            );
            $newLines = explode("\n", $line);
            $wrappedLines = array_merge($wrappedLines, $newLines);
        }
        return $wrappedLines;
    }

    private function getCategory($id) {
        $arr = config('blog.categories');
        if (array_key_exists($id, $arr))
            return $arr[$id]['name'];
        return $id;
    }
}
