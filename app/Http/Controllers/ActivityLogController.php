<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\ArticleResource;
use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\Reservation;
use App\Models\Revision;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Carbon\Carbon;

class ActivityLogController extends BaseController {

    public function index(Request $request) {
        $query = $this->validate($request, [
            'id' => ['string'],
            'timestamp' => ['string'],
            'guest_id' => ['string'],
            'exh_id' => ['string'],
            'log_type' => ['string'],
            'reservation_id' => ['string'],
        ]);
        $log = ActivityLog::query();

        foreach ($query as $i => $value) {
            if ($i == 'reservation_id') {
                if (!$request->user()->hasPermission('reservation')) {
                    abort(403);
                }
                if ($reservation = Reservation::find($value)) {
                    $log->where('guest_id', $reservation->guest->id);
                } else return response([]);
            }
            $log->where($i, $value);
        }

        return response()->json(ActivityLogResource::collection($log->get()));
    }
}
