<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReservationResource;
use App\Http\Resources\ReservationWithPrivateResource;
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

        foreach ($query as $i => $value) $response->where($i, $value);


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

        do {
            $reservation_id = 'R-'.Str::random(10);
        } while (Reservation::where('id', $reservation_id)->exists());

        $reservation = Reservation::create(
            array_merge($body, ['id' => $reservation_id])
        );

        return response($reservation, 201);
    }

    public function show($id) {
        $reservation = Reservation::find($id);
        if (!$reservation) abort(404);

        return response()->json(new ReservationWithPrivateResource($reservation));
    }

    public function check($id) {
        $reservation = Reservation::find($id);
        if (!$reservation) abort(404);

        $status_code = $reservation->getErrorCode();
        if ($status_code !== null) {
            $valid = false;
        } else {
            $valid = true;
        }

        $res = [
            'valid' => $valid,
            'status_code' => $status_code,
            'term_id' => $reservation->term_id
        ];

        return response()->json($res);
    }
}
