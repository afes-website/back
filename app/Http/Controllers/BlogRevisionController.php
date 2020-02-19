<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Revision;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Carbon\Carbon;

class BlogRevisionController extends Controller {
    public function get_revision_list(Request $request){
        return response()->json(Revision::all());
    }

    public function get_revision($id){
        $revision = Revision::find($id);

        if(!$revision)
            throw new HttpException(404);

        $revision -> timestamp = date(DATE_ISO8601, $revision -> timestamp);
        $article_info = $revision;
        return response()->json($article_info, 200);
    }

    public function create_revision(Request $request) {
        $validated_request = $this->validate($request, [
            'title' => ['required'],
            'article_id'=> ['string'],
            'user_id'=> ['string'],
            'content' => ['required', 'string'],
        ]);
        return response()->json(Revision::create($validated_request), 201);
    }
}
