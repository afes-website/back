<?php
namespace Tests;

/*
 * general/log/
 */

use App\Http\Resources\GuestResource;
use App\Models\ActivityLog;
use App\Models\Exhibition;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Term;
use App\Models\User;

class LogTest extends TestCase {
    public function testData() {
        $term = factory(Term::class)->create();
        $reservation = factory(Reservation::class)->create([
            'term_id' => $term->id
        ]);
        $guest = factory(Guest::class)->create([
            'reservation_id' => $reservation->id
        ]);
        $exh_user = factory(User::class, 'exhibition')->create();
        $exh = factory(Exhibition::class)->create([
            'id' => $exh_user->id
        ]);
        $log = factory(ActivityLog::class)->create([
            'exh_id' => $exh->id,
            'guest_id' => $guest->id,
        ]);

        $this->actingAs($exh_user)->get('/onsite/general/log');
        $this->assertResponseOk();
        $this->receiveJson();
        $this->seeJsonEquals([
            [
                'id' => $log->id,
                'timestamp' => $log->timestamp->toIso8601ZuluString(),
                'exh_id' => $exh->id,
                'guest' => new GuestResource($guest),
                'log_type' => $log->log_type,
            ],
        ]);
    }

    public function testGetPermission() {
        foreach (['general', 'exhibition', 'reservation'] as $perm) {
            $user = factory(User::class, $perm)->create();

            $this->actingAs($user)->get('/onsite/general/log');
            $this->assertResponseOk();
        }

        foreach (['blogAdmin', 'admin', 'blogWriter', 'teacher'] as $perm) {
            $user = factory(User::class, $perm)->create();
            $this->actingAs($user)->get('/onsite/general/log');
            $this->assertResponseStatus(403);
        }
    }
}
