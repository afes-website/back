<?php

namespace App\Http\Controllers;

use App\Exceptions\HttpExceptionWithErrorCode;
use App\Http\Resources\GuestResource;
use App\Models\Guest;
use App\Models\Image;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\SlackNotify;
use Carbon\Carbon;
use App\Models\ActivityLog;


class GuestController extends Controller {
    public function show(Request $request, $id){
        $guest = Guest::find($id);
        if(!$guest){
            abort(404);
        }

        return response()->json(new GuestResource($guest));
    }

    public function index(){
        return response()->json(GuestResource::collection((Guest::all())));
    }

    public function enter(Request $request){
        $this->validate($request, [
            'reservation_id' => ['string', 'required'],
            'guest_id' => ['string', 'required']
        ]);

        if(!preg_match('/^[A-Z]{2,3}-[a-zA-Z0-9]{5}$/', $request->guest_id)){
            throw new HttpExceptionWithErrorCode(409, 'INVALID_WRISTBAND_CODE');
        }

        $reserv = Reservation::find($request->reservation_id);

        if(!$reserv) throw new HttpExceptionWithErrorCode(404, 'RESERVATION_NOT_FOUND');

        if(Guest::where('reservation_id', $reserv)->exists()) {
            throw new HttpExceptionWithErrorCode(409, 'ALREADY_ENTERED_RESERVATION');
        }

        if(Guest::find($request->guest_id)) {
            throw new HttpExceptionWithErrorCode(409, 'ALREADY_USED_WRISTBAND');
        }

        $term = $reserv->term;
        $current = Carbon::now();
        if(
            !preg_match(
                '/^'.config('manage.colors')[$term->color_id]['prefix'].'/',
                $request->guest_id
            )
        ) {
            throw new HttpExceptionWithErrorCode(400, 'WRONG_WRISTBAND_COLOR');
        }

        if(
            new Carbon($term->enter_scheduled_time) > $current
            || new Carbon($term->exit_scheduled_time) < $current
        ) {
            throw new HttpExceptionWithErrorCode(400, 'OUT_OF_RESERVATION_TIME');
        }

        $wb_prefix = $term->color_id.'-';

        // TODO: wristBand の term が一致するかのチェック

        if(strpos($request->guest_id, $wb_prefix) !== 0){
            throw new HttpExceptionWithErrorCode(400, 'WRONG_WRISTBAND_COLOR');
        }


        $guest = Guest::create(
            [
                'id' => $request->guest_id,
                'term_id' => $term->id,
                'reservation_id' => $request->reservation_id
            ]
        );

        $reserv->update(['guest_id' => $guest->id]);

        return response()->json(new GuestResource($guest));
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

        return response()->json($guest);
    }

    public function show_log(Request $request, $id){
        $logs = ActivityLog::query()->where('id', $id)->get();
        return response()->json($logs);
    }
}
