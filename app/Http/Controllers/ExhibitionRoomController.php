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
}
