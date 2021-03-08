<?php
namespace Tests;

use App\Models\Reservation;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Str;

class GeneralEntranceTest extends TestCase {
    public function testEnter() {
        $user = factory(User::class, 'general')->create();
        $term = factory(Term::class)->create();
        $reservation = factory(Reservation::class)->create([
            'term_id' => $term->id
        ]);
        $guest_id = config('onsite.guest_types')[$term->guest_type]['prefix']."-".Str::random(5);
        $this->actingAs($user)->post(
            '/onsite/general/enter',
            ['guest_id' => $guest_id, 'reservation_id' => $reservation->id]
        );
        $this->assertResponseOk();
    }
}
