<?php

namespace App\Http\Controllers;

use App\Http\Resources\TermResource;
use App\Models\Term;

class TermController extends Controller {
    public function index() {
        $terms = Term::all();
        $result = [];
        foreach ($terms as $term) {
            $result[$term->id] = [
                "enter_scheduled_time" => $term->enter_scheduled_time,
                "exit_scheduled_time" => $term->exit_scheduled_time,
                "guest_type" => $term->guest_type
            ];
        }

        return response()->json($result);
    }
}
