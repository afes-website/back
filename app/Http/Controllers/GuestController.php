<?php

namespace App\Http\Controllers;

use App\Exceptions\HttpExceptionWithErrorCode;
use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\GuestResource;
use App\Models\Guest;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ActivityLog;

class GuestController extends Controller {
    public function show(Request $request, $id) {
        $guest = Guest::find($id);
        if (!$guest) {
            abort(404);
        }

        return response()->json(new GuestResource($guest));
    }

    public function index() {
        return response()->json(GuestResource::collection(Guest::all()));
    }

    public function enter(Request $request) {
        $this->validate($request, [
            'reservation_id' => ['string', 'required'],
            'guest_id' => ['string', 'required']
        ]);

        if (!preg_match('/^[A-Z]{2,3}-[a-zA-Z0-9]{5}$/', $request->guest_id)) {
            throw new HttpExceptionWithErrorCode(400, 'INVALID_WRISTBAND_CODE');
        }

        $reservation = Reservation::find($request->reservation_id);

        if (!$reservation) throw new HttpExceptionWithErrorCode(400, 'RESERVATION_NOT_FOUND');

        $reservation_error_code = $reservation->getErrorCode();

        if ($reservation_error_code !== null) {
            throw new HttpExceptionWithErrorCode(400, $reservation_error_code);
        }

        if (Guest::find($request->guest_id)) {
            throw new HttpExceptionWithErrorCode(400, 'ALREADY_USED_WRISTBAND');
        }

        $term = $reservation->term;

        if (strpos($request->guest_id, config('onsite.colors')[$term->color_id]['prefix']) !== 0
        ) {
            throw new HttpExceptionWithErrorCode(400, 'WRONG_WRISTBAND_COLOR');
        }


        $guest = Guest::create(
            [
                'id' => $request->guest_id,
                'term_id' => $term->id,
                'reservation_id' => $request->reservation_id
            ]
        );

        // TODO: 複数人で処理するときの扱いを考える (docsの編集待ち)
        $reservation->update(['guest_id' => $guest->id]);

        return response()->json(new GuestResource($guest));
    }

    public function exit(Request $request) {
        $this->validate($request, [
            'guest_id' => ['string', 'required']
        ]);

        $guest = Guest::find($request->guest_id);
        if (!$guest) {
            abort(404);
        }

        if ($guest->exited_at !== null) {
            abort(409);
        }

        $guest->update(['exited_at' => Carbon::now()]);

        return response()->json(new GuestResource($guest));
    }

    public function showLog(Request $request, $id) {
        $guest = Guest::find($id);
        if (!$guest) {
            abort(404);
        }
        $logs = ActivityLog::query()->where('guest_id', $id)->get();
        return response()->json(ActivityLogResource::collection($logs));
    }
}
