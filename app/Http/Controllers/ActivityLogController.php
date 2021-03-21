<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\ArticleResource;
use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\Revision;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Carbon\Carbon;

class ActivityLogController extends BaseController {

    public function index(Request $request) {
        $query = $this->validate($request, [
            'id' => ['string'],
            'timestamp' => ['string'],
            'guest_id' => ['int'],
            'exh_id' => ['string'],
            'log_type' => ['string'],
            'reservation_id' => ['string'],
        ]);
        $response = ActivityLog::query();

        if ($request->has('reservation_id') && !$request->user()->hasPermission())
            abort(403);

        return response()->json(ActivityLogResource::collection($response->get()));
    }
}
