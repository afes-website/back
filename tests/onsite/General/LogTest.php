<?php
namespace Tests;

/*
 * general/log/
 */

use App\Models\User;

class LogTest extends TestCase {
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
