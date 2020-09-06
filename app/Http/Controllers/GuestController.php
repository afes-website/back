<?php

namespace App\Http\Controllers;

use App\Exceptions\HttpExceptionWithErrorCode;
use App\Models\Guest;
use App\Models\Image;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\SlackNotify;
use Carbon\Carbon;


class GuestController extends Controller {
    public function enter(Request $request){
        $this->validate($request, [
            'reservation_id' => ['string', 'required'],
            'guest_id' => ['string', 'required']
        ]);

        $reserv = Reservation::find($request->reservation_id);

        if(!$reserv) throw new HttpExceptionWithErrorCode(404, 'RESERVATION_NOT_FOUND');

        if(Guest::where('reservation_id', $reserv)->exists()) {
            throw new HttpExceptionWithErrorCode(409, 'ALREADY_ENTERED_RESERVATION');
        }

        // TODO: wristBand 形式チェック

        // TODO: wristBand の重複チェック
        if(Guest::find($request->guest_id)) {
            throw new HttpExceptionWithErrorCode(409, 'ALREADY_USED_WRISTBAND');
        }

        $term = $reserv->term;
        $current = Carbon::now()->timestamp;
        if(
            !preg_match(
                '/^'.config('manage.colors')[$term->color_id]['prefix'].'/',
                $request->guest_id
            )
        ) {
            throw new HttpExceptionWithErrorCode(400, 'WRONG_WRISTBAND_COLOR');
        }

        if(
            $term->enter_scheduled_time > $current
            || $term->exit_scheduled_time < $current
        ) {
            throw new HttpExceptionWithErrorCode(400, 'OUT_OF_RESERVATION_TIME');
        }

        $exit_time = $term->exit_scheduled_time;
        $color_id = $term->color_id;

        // TODO: wristBand の term が一致するかのチェック

        $guest = Guest::create(
            [
                'id' => $request->guest_id,
                'term_id' => $term->id,
                'reservation_id' => $request->reservation_id
            ]
        );

        return response()->json($guest);
    }

    public function exit(Request $request){
        $this->validate($request, [
            'guest_id' => ['string', 'required']
        ]);

        $guest = Guest::find($request->guest_id);
        if(!$guest) {
            abort(404);
        }

        if($guest->exited_at != NULL) {
            abort(409);
        }

        $guest->update(['exited_at' => Carbon::now()]);

        return response()->json();
    }
}
