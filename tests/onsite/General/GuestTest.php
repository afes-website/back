<?php
namespace Tests;

/*
 * general/guest/*
 *
 * /:get /_id:get /_id/log:get
 */

use App\Models\Guest;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Carbon;

class GuestTest extends TestCase {
    public function testGetAll() {
        $term_count = 3;
        $guest_count = 3;
        $user = factory(User::class, 'general')->create();

        for ($i = 0; $i < $term_count; $i++) {
            $term[] = factory(Term::class)->create();
            for ($j = 0; $j < $guest_count; $j++) {
                $guest[] = factory(Guest::class)->create([
                    'term_id' => $term[$i]->id
                ]);
            }
        }

        $this->actingAs($user)->get('/onsite/general/guest');
        $this->assertResponseOk();
        $this->receiveJson();
        $res = json_decode($this->response->getContent());
        $this->assertCount($term_count * $guest_count, $res);
    }

    public function testShow() {
        $user = factory(User::class, 'general')->create();
        $term = factory(Term::class)->create();

        $guest = factory(Guest::class)->create([
            'term_id' => $term->id,
        ]);

        $this->actingAs($user)->get('/onsite/general/guest/' . $guest->id);
        $this->assertResponseOk();
        $this->receiveJson();
        $res = json_decode($this->response->getContent());

        $this->seeJsonEquals([
            'id' => $guest->id,
            'entered_at' => $guest->entered_at,
            'exited_at' => $guest->exited_at,
            'exh_id' => $guest->exh_id,
            'term' => [
                'enter_scheduled_time' => $term->enter_scheduled_time->toIso8601ZuluString(),
                'exit_scheduled_time' => $term->exit_scheduled_time->toIso8601ZuluString(),
                'guest_type' => $term->guest_type
            ]
        ]);
    }
}
