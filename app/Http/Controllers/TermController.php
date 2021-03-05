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
                "color_id" => $term->color_id
            ];
        }

        return response()->json($result);
    }
}
