<?php

namespace App\Http\Controllers;

use App\Models\Term;

class TermController extends Controller {
    public function index() {
        $terms = Term::all();
        $result = [];
        foreach ($terms as $term) {
            $result[$term->id] = [
                "enter_scheduled_time" => $term->enter_scheduled_time,
                "exit_scheduled_time" => $term->exit_scheduled_time,
                "prefix" => config('onsite.guest_types')[$term->guest_type]['prefix']
            ];
        }

        return response()->json($result);
    }
}
