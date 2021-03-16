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
}
