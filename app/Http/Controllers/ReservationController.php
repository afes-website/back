<?php

namespace App\Http\Controllers;

use App\Http\Resources\RevisionResource;
use App\Models\Reservation;
use App\Models\Revision;
use App\Models\WriterUser;
use Illuminate\Http\Request;
use App\SlackNotify;
use \Illuminate\Support\Str;


class ReservationController extends Controller {
    public function create(Request $request) {
        $this->validate($request, [
            'email' => ['required', 'string', 'email:rfc,dns'],
            'term_id' => ['required', 'string'],
            'people_count' => ['required', 'integer', 'gte:1'],
            'name' => ['required', 'string'],
            'address' => ['required', 'string'],
            'cellphone' => ['required', 'string', 'regex:/0\d{9,10}$/']
        ]);

        $reservation_id = 'R_'.Str::random(10);
        while(true){
            if(!Revision::where('article_id', $reservation_id)->exists()) break;
            $reservation_id = 'R_'.Str::random(10);
        }

        $reservation = Reservation::create(
            array_merge($request->all(), ['id' => $reservation_id])
        );

        return response($reservation,201);
    }

}
