<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\SlackNotify;

class ImageController extends Controller {
    public function show(Request $request, $id){
        $image = Image::find($id);
        if(!$image) abort(404);
        $content = $image->content;

        if(!$request->has('orig')) {
            $img = \Intervention\Image\Facades\Image::make($content);
            if ($request->has('h') && $request->has('w'))
                $img->fit($request->input('w'), $request->input('h'));
            elseif($request->has('h'))
                $img->heighten($request->input('h'));
            elseif($request->has('w'))
                $img->widen($request->input('w'));
            else {
                if($img->width() > 1080)
                    $img->widen(1080);
                if($img->height() > 600)
                    $img->heighten(600);
            }
            $content = $img->encode($image->mime_type);
        }

        return response($content)
            ->header('Content-Type', $image->mime_type)
            ->header('Last-Modified', $image->created_at->toRfc7231String());
    }

    public function create(Request $request) {
        if (!$request->hasFile('content'))abort(400, 'file is not uploaded');
        $file = $request->file('content');
        if (substr($file->getMimeType(), 0, 6) !== 'image/')abort(400, 'uploaded file is not an image');

        $content = $file->get();

        $id = Str::random(40);

        $image = Image::create([
            'id' => $id,
            'content' => $content,
            'user_id' => $request->user('writer')->id,
            'mime_type' => $file->getMimeType()
        ]);

        SlackNotify::notify_image($image, 'uploaded', $request->user('writer')->name);

        return response()->json(['id' => $id], 201);
    }
}
