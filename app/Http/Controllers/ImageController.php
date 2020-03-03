<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImageController extends Controller {
    public function show(Request $request, $id){
        $image = Image::find($id);
        if(!$image) abort(404);
        $content = $image->content;

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

        Image::create([
            'id' => $id,
            'content' => $content,
            'user_id' => $request->user('writer')->id,
            'mime_type' => $file->getMimeType()
        ]);


        return response()->json(['id' => $id], 201);
    }
}
