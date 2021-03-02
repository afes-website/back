<?php

namespace App\Http\Controllers;

use App\Exceptions\HttpExceptionWithErrorCode;
use App\Http\Resources\ExhibitionRoomResource;
use App\Http\Resources\GuestResource;
use App\Models\ExhibitionRoom;
use App\Models\Guest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ActivityLog;

class ExhibitionRoomController extends Controller {
    public function index() {
        $exh_status = ExhibitionRoom::all();
        $terms = array();
        foreach ($exh_status as $exh) {
            $exh_term = $exh->countGuest();
            foreach ($exh_term as $id => $count) {
                if (isset($terms[$id])) $terms[$id] += $count;
                else $terms[$id] = $count;
            }
        }
        return response()->json([
            'exh' => $exh_status,
            'all' => $terms
        ]);
    }

    public function show(Request $request, $id) {
        $exhibition = ExhibitionRoom::find($id);
        if (!$exhibition) {
            abort(404);
        }

        return response()->json(new ExhibitionRoomResource($exhibition));
    }

    public function enter(Request $request) {
        $this->validate($request, [
            'guest_id' => ['string', 'required']
        ]);

        $user_id = $request->user()->id;
        $guest = Guest::find($request->guest_id);
        $exh = ExhibitionRoom::find($user_id);
        $current = Carbon::now();

        if (!$guest) throw new HttpExceptionWithErrorCode(400, 'GUEST_NOT_FOUND');

        if ($guest->exh_id === $user_id)
            throw new HttpExceptionWithErrorCode(400, 'GUEST_ALREADY_ENTERED');

        if ($exh->capacity === $exh->guest_count)
            throw new HttpExceptionWithErrorCode(400, 'PEOPLE_LIMIT_EXCEEDED');

        if ($guest->exited_at !== null)
            throw new HttpExceptionWithErrorCode(400, 'GUEST_ALREADY_EXITED');

        if (new Carbon($guest->term->exit_scheduled_time) < $current)
            throw new HttpExceptionWithErrorCode(400, 'EXIT_TIME_EXCEEDED');


        $guest->update(['exh_id' => $exh->id]);

        ActivityLog::create([
            'exh_id' => $exh->id,
            'log_type' => 'enter',
            'guest_id' => $guest->id
        ]);

        return response()->json(new GuestResource($guest));
    }

    public function exit(Request $request) {
        $this->validate($request, [
            'guest_id' => ['string', 'required']
        ]);

        $user_id = $request->user()->id;
        $guest = Guest::find($request->guest_id);
        $exh = ExhibitionRoom::find($user_id);

        if (!$guest) throw new HttpExceptionWithErrorCode(400, 'GUEST_NOT_FOUND');

        if ($guest->exited_at !== null)
            throw new HttpExceptionWithErrorCode(400, 'GUEST_ALREADY_EXITED');

        $guest->update(['exh_id' => null]);

        ActivityLog::create([
            'exh_id' => $exh->id,
            'log_type' => 'exit',
            'guest_id' => $guest->id
        ]);

        return response()->json(new GuestResource($guest));
    }

    public function showLog(Request $request) {
        $id = $request->user()->id;
        $guest = ExhibitionRoom::find($id);
        if (!$guest) {
            abort(500, 'ExhibitionRoom Not found');
        }
        $logs = ActivityLog::query()->where('exh_id', $id)->get();
        return response()->json($logs);
    }
}
