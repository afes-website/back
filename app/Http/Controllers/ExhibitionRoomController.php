<?php

namespace App\Http\Controllers;

use App\Exceptions\HttpExceptionWithErrorCode;
use App\Http\Resources\ExhibitionRoomResource;
use App\Http\Resources\GuestResource;
use App\Models\ExhibitionRoom;
use App\Models\Guest;
use App\Models\Image;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\SlackNotify;
use Carbon\Carbon;
use App\Models\ActivityLog;


class ExhibitionRoomController extends Controller {
    public function show(Request $request, $id){
        $exhibition = ExhibitionRoom::find($id);
        if(!$exhibition){
            abort(404);
        }

        return response()->json(new ExhibitionRoomResource($exhibition));
    }

    public function enter(Request $request){
        $this->validate($request, [
            'guest_id' => ['string', 'required']
        ]);

        $user_id = $request->user()->id;
        $guest = Guest::find($request->guest_id);
        $exh = ExhibitionRoom::find($user_id);
        $current = Carbon::now();

        if(!$guest) throw new HttpExceptionWithErrorCode(400, 'GUEST_NOT_FOUND');

        if($guest->exh_id === $user_id)
            throw new HttpExceptionWithErrorCode(400, 'GUEST_ALREADY_ENTERED');

        if($exh->capacity === $exh->guest_count)
            throw new HttpExceptionWithErrorCode(400, 'PEOPLE_LIMIT_EXCEEDED');

        if($guest->exited_at !== NULL)
            throw new HttpExceptionWithErrorCode(400, 'GUEST_ALREADY_EXITED');

        if(
            new Carbon($guest->term->exit_scheduled_time) < $current
        )
            throw new HttpExceptionWithErrorCode(400, 'EXIT_TIME_EXCEEDED');


        $guest->update(['exh_id' => $exh->id]);

        ActivityLog::create([
            'exh_id' => $exh->id,
            'log_type' => 'enter',
            'guest_id' => $guest->id
        ]);

        return response()->json(new GuestResource($guest));
    }
}
