<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Illuminate\Http\Request;
use \Illuminate\Support\Str;


class ReservationController extends Controller {

    public function index(Request $request) {
        $query = $this->validate($request, [
            'email' => ['string', 'email:rfc,dns'],
            'term_id' => ['string'],
            'people_count' => ['integer', 'gte:1'],
            'name' => ['string'],
            'address' => ['string'],
            'cellphone' => ['string', 'regex:/0\d{9,10}$/']
        ]);

        $response = Reservation::query();

        foreach ($query as $i => $value){
            if ($i === 'q')continue;
            if ($i === 'author_id')continue;
            $response->where($i, $value);
        }

        return response(ReservationResource::collection($response->get()));
    }
    public function create(Request $request) {
        $body = $this->validate($request, [
            'email' => ['required', 'string', 'email:rfc,dns'],
            'term_id' => ['required', 'string'],
            'people_count' => ['required', 'integer', 'gte:1'],
            'name' => ['required', 'string'],
            'address' => ['required', 'string'],
            'cellphone' => ['required', 'string', 'regex:/0\d{9,10}$/']
        ]);

        $reservation_id = 'R_'.Str::random(10);
        while(true){
            if(!Reservation::where('id', $reservation_id)->exists()) break;
            $reservation_id = 'R_'.Str::random(10);
        }

        $reservation = Reservation::create(
            array_merge($body, ['id' => $reservation_id])
        );

        return response($reservation,201);
    }

}
